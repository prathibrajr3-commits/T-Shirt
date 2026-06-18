<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Payment;
use App\Models\ShippingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.list')->with('error', 'Your cart is empty.');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $user = auth()->user();
        $shippingSettings = ShippingSetting::getSettings();

        return view('shop.checkout', compact('cart', 'total', 'user', 'shippingSettings'));
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.list')->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|in:cod,razorpay',
        ]);

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $shippingSettings = ShippingSetting::getSettings();
        $shipping = $shippingSettings->calculateShipping($total);
        $finalTotal = $total + $shipping;

        // Double check stock levels for all items
        foreach ($cart as $item) {
            $product = Product::find($item['id']);
            if (!$product || $product->stock < $item['quantity']) {
                return back()->with('error', "Product '" . ($product ? $product->name : 'Unknown') . "' is out of stock or does not have enough inventory.")
                    ->withInput();
            }
        }

        if ($request->payment_method === 'cod') {
            try {
                DB::beginTransaction();

                $orderNumber = 'TS-' . strtoupper(uniqid());

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'order_number' => $orderNumber,
                    'status' => 'pending',
                    'total_amount' => $finalTotal,
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'shipping_address' => $request->shipping_address,
                    'phone' => $request->phone,
                ]);

                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'size' => $item['size'],
                        'color' => $item['color'],
                    ]);

                    $product = Product::find($item['id']);
                    $product->decrement('stock', $item['quantity']);
                }

                // Log COD payment details
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => null,
                    'payment_method' => 'cod',
                    'amount' => $finalTotal,
                    'payment_status' => 'pending',
                ]);

                DB::commit();

                // Clear session cart
                session()->forget('cart');

                return redirect()->route('orders.show', $order->id)->with('success', 'Order placed successfully! Order Number: ' . $orderNumber);
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage())->withInput();
            }
        }

        if ($request->payment_method === 'razorpay') {
            try {
                $keyId = config('razorpay.key_id');
                $keySecret = config('razorpay.key_secret');

                if (empty($keyId) || empty($keySecret)) {
                    throw new \Exception("Razorpay credentials are not configured in your environment.");
                }

                $amountInPaise = round($finalTotal * 100);

                // Create Razorpay Order via REST API
                $response = Http::withBasicAuth($keyId, $keySecret)
                    ->post('https://api.razorpay.com/v1/orders', [
                        'amount' => $amountInPaise,
                        'currency' => 'INR',
                        'receipt' => 'rcpt_' . uniqid(),
                    ]);

                if ($response->failed()) {
                    throw new \Exception("Razorpay API error: " . ($response->json('error.description') ?? $response->body()));
                }

                $razorpayOrder = $response->json();

                // Store temporary checkout state in session
                session([
                    'checkout_phone' => $request->phone,
                    'checkout_address' => $request->shipping_address,
                ]);

                return view('shop.razorpay_payment', [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => $amountInPaise,
                    'key_id' => $keyId,
                    'user' => auth()->user(),
                    'phone' => $request->phone,
                    'shipping_address' => $request->shipping_address,
                    'total_amount' => $finalTotal,
                ]);

            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        }
    }

    public function razorpayCallback(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
        ]);

        $razorpayPaymentId = $request->razorpay_payment_id;
        $razorpayOrderId = $request->razorpay_order_id;
        $razorpaySignature = $request->razorpay_signature;

        $keySecret = config('razorpay.key_secret');

        // Secure Signature Verification
        $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $keySecret);
        if (!hash_equals($expectedSignature, $razorpaySignature)) {
            return redirect()->route('checkout.index')->with('error', 'Payment signature verification failed. Unauthorized transaction.');
        }

        // Prevent duplicate processing
        $existingPayment = Payment::where('transaction_id', $razorpayPaymentId)->first();
        if ($existingPayment) {
            return redirect()->route('orders.show', $existingPayment->order_id)
                ->with('success', 'Order was already placed successfully.');
        }

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.list')->with('error', 'Your cart is empty.');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $shippingSettings = ShippingSetting::getSettings();
        $shipping = $shippingSettings->calculateShipping($total);
        $finalTotal = $total + $shipping;

        try {
            DB::beginTransaction();

            // Double check stock levels for all items
            foreach ($cart as $item) {
                $product = Product::find($item['id']);
                if (!$product || $product->stock < $item['quantity']) {
                    throw new \Exception("Product '" . ($product ? $product->name : 'Unknown') . "' is out of stock or does not have enough inventory.");
                }
            }

            $orderNumber = 'TS-' . strtoupper(uniqid());

            // Create Order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_amount' => $finalTotal,
                'payment_method' => 'razorpay',
                'payment_status' => 'completed',
                'shipping_address' => $request->shipping_address,
                'phone' => $request->phone,
            ]);

            // Create OrderItems & Deduct Stock
            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'size' => $item['size'],
                    'color' => $item['color'],
                ]);

                $product = Product::find($item['id']);
                $product->decrement('stock', $item['quantity']);
            }

            // Save Payment Details
            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $razorpayPaymentId,
                'payment_method' => 'razorpay',
                'amount' => $finalTotal,
                'payment_status' => 'completed',
                'raw_response' => $request->all(),
            ]);

            DB::commit();

            // Clear session cart and temporary data
            session()->forget('cart');
            session()->forget(['checkout_phone', 'checkout_address']);

            return redirect()->route('orders.show', $order->id)->with('success', 'Order placed successfully via Razorpay! Order Number: ' . $orderNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('checkout.index')->with('error', 'Error processing order: ' . $e->getMessage());
        }
    }
}

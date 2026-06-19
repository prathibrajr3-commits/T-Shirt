<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $discount = 0.00;
        $couponCode = null;

        if (session()->has('coupon')) {
            $couponData = session()->get('coupon');
            $coupon = \App\Models\Coupon::where('code', $couponData['code'])->first();
            $errorMsg = null;
            if ($coupon && $coupon->isValidForUser(auth()->user(), $total, $errorMsg)) {
                $discount = $coupon->calculateDiscount($total);
                $couponCode = $coupon->code;
            } else {
                session()->forget('coupon');
                session()->flash('error', $errorMsg ?: 'Invalid coupon code');
            }
        }

        return view('shop.cart', compact('cart', 'total', 'discount', 'couponCode'));
    }

    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'size' => 'required|string',
            'color' => 'required|string',
            'quantity' => 'required|integer|min:1|max:' . $product->stock,
        ]);

        $size = $request->size;
        $color = $request->color;
        $qty = $request->quantity;

        // Check if color and size exist on product
        if (!in_array($size, $product->sizes) || !in_array($color, $product->colors)) {
            return back()->with('error', 'Selected size or color is invalid.');
        }

        $cart = session()->get('cart', []);
        $cartKey = $id . '-' . $size . '-' . $color;

        // If product already in cart, update quantity
        if (isset($cart[$cartKey])) {
            $newQty = $cart[$cartKey]['quantity'] + $qty;
            if ($newQty > $product->stock) {
                return back()->with('error', 'Cannot add more. Exceeds available stock.');
            }
            $cart[$cartKey]['quantity'] = $newQty;
        } else {
            // Get correct price (base price or discount price)
            $price = $product->discount_price !== null ? $product->discount_price : $product->price;

            $cart[$cartKey] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $price,
                'quantity' => $qty,
                'size' => $size,
                'color' => $color,
                'image_path' => $product->image_path,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Product added to cart!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);
        $key = $request->key;
        $qty = $request->quantity;

        if (isset($cart[$key])) {
            $product = Product::find($cart[$key]['id']);
            if ($product && $qty > $product->stock) {
                return back()->with('error', 'Requested quantity exceeds available stock.');
            }
            $cart[$key]['quantity'] = $qty;
            session()->put('cart', $cart);
            return back()->with('success', 'Cart updated successfully.');
        }

        return back()->with('error', 'Item not found in cart.');
    }

    public function remove($key)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
            return back()->with('success', 'Item removed from cart.');
        }

        return back()->with('error', 'Item not found in cart.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->code;
        $coupon = \App\Models\Coupon::where('code', $code)->first();

        if (!$coupon) {
            return back()->with('error', 'Invalid coupon code');
        }

        // Calculate subtotal of cart
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $errorMsg = null;
        if (!$coupon->isValidForUser(auth()->user(), $subtotal, $errorMsg)) {
            return back()->with('error', $errorMsg ?: 'Invalid coupon');
        }

        // Store coupon in session
        session()->put('coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
        ]);

        return back()->with('success', 'Coupon applied successfully.');
    }

    public function removeCoupon()
    {
        session()->forget('coupon');
        return back()->with('success', 'Coupon removed successfully.');
    }
}

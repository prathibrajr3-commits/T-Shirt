<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);
        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['items.product', 'histories.user'])->findOrFail($id);

        \Illuminate\Support\Facades\Gate::authorize('view', $order);

        $milestones = $order->getMilestones();

        return view('orders.show', compact('order', 'milestones'));
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // 1. Prevent double cancellation
        if ($order->status === 'cancelled') {
            return back()->with('error', 'Order is already cancelled.');
        }

        // 2. Block cancellation after payment capture
        if (in_array($order->payment_status, ['completed', 'paid']) && !in_array($order->status, ['pending', 'confirmed'])) {
            abort(403);
        }

        // 3. Policy Security
        \Illuminate\Support\Facades\Gate::authorize('cancel', $order);

        // 4. Validation
        $request->validate([
            'cancel_reason' => 'required|string|in:Ordered by mistake,Found a better price,Delivery takes too long,Want to change product,Other',
            'reason_details' => 'required_if:cancel_reason,Other|nullable|string|max:1000',
        ]);

        $reason = $request->cancel_reason;
        if ($reason === 'Other') {
            $reason = $request->reason_details;
        }

        // 5. Business Rules - Update fields
        $now = now();
        $order->status = Order::STATUS_CANCELLED;
        $order->customer_cancelled_at = $now;
        $order->cancelled_at = $now;
        $order->status_changed_at = $now;
        $order->cancelled_by = 'customer';
        $order->customer_cancel_reason = $reason;
        $order->save();

        // 6. Order History
        $order->histories()->create([
            'status' => Order::STATUS_CANCELLED,
            'notes' => "Order cancelled by customer.\nReason:\n" . $reason,
            'updated_by' => auth()->id(),
        ]);

        // 7. Notifications
        // Send confirmation notification to customer
        $order->user->notify(new \App\Notifications\CustomerOrderCancelledNotification($order));

        // Send notification to admin
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AdminOrderCancelledNotification($order));
        }

        return redirect()->route('orders.show', $order->id)->with('success', 'Your order has been cancelled successfully.');
    }

    public function requestReturn(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Security Check
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Return request only for delivered orders
        if ($order->status !== Order::STATUS_DELIVERED) {
            return back()->with('error', 'Only delivered orders can be returned.');
        }

        // 7-day return window enforced
        if (!$order->delivered_at || $order->delivered_at->addDays(7)->isPast()) {
            return back()->with('error', 'Return window expired.');
        }

        // Only one active return request per order
        $hasActive = $order->returnRequests()
            ->whereIn('status', [\App\Models\ReturnRequest::STATUS_PENDING, \App\Models\ReturnRequest::STATUS_APPROVED, \App\Models\ReturnRequest::STATUS_COMPLETED])
            ->exists();

        if ($hasActive) {
            return back()->with('error', 'A return request already exists for this order.');
        }

        // Validation
        $rules = [
            'reason' => 'required|in:Wrong Size,Wrong Product,Damaged Product,Quality Issue,Changed Mind,Other',
            'description' => 'nullable|string|max:1000',
            'image' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg',
                'max:5120', // 5MB
                \Illuminate\Validation\Rule::requiredIf(function () use ($request) {
                    return $request->reason === 'Damaged Product';
                }),
            ],
        ];

        $request->validate($rules);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('returns', 'public');
        }

        $returnRequest = \App\Models\ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'description' => $request->description,
            'image' => $imagePath,
            'status' => \App\Models\ReturnRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        // Add Return Audit History
        $order->histories()->create([
            'status' => 'Return Requested',
            'notes' => "Return request submitted.\nReason: " . $request->reason . ($request->description ? "\nDescription: " . $request->description : ""),
            'updated_by' => auth()->id(),
        ]);

        // Notify admin when: New return request submitted
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AdminReturnRequestSubmittedNotification($returnRequest));
        }

        return redirect()->route('orders.show', $order->id)->with('success', 'Your return request has been submitted successfully.');
    }
}

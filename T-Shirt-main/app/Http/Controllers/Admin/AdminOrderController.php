<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order_number, customer name, email, or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sort by newest/oldest
        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.product', 'histories.user'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'shipping_provider' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:255',
            'payment_status' => 'required|in:pending,completed,failed',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Enforce valid status transitions
        if (!$order->isValidTransition($request->status)) {
            return back()->with('error', 'Invalid order status transition.');
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Save status change
        $order->payment_status = $request->payment_status;
        $order->updateStatus(
            $newStatus,
            $request->notes,
            $request->shipping_provider,
            $request->tracking_number,
            $request->tracking_url,
            auth()->id()
        );

        // Send email notification on status change if status is one of target statuses
        if ($oldStatus !== $newStatus && in_array($newStatus, ['confirmed', 'shipped', 'delivered', 'cancelled'])) {
            $order->user->notify(new OrderStatusUpdatedNotification($order));
        }

        return back()->with('success', 'Order status updated successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->latest()->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:255',
            'payment_status' => 'required|in:pending,completed,failed',
        ]);

        $order->status = $request->status;
        $order->payment_status = $request->payment_status;
        
        if ($request->filled('tracking_number')) {
            $order->tracking_number = $request->tracking_number;
        }

        // Auto complete payment if delivered
        if ($order->status === 'delivered') {
            $order->payment_status = 'completed';
        }

        $order->save();

        return back()->with('success', 'Order status updated successfully.');
    }
}

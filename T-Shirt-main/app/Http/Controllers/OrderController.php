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
        $order = Order::with(['items.product'])->findOrFail($id);

        // Security check: ensure user owns the order, unless user is admin
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        // Stepper mapping
        $statuses = ['pending', 'processing', 'shipped', 'delivered'];
        $currentStatusIndex = array_search($order->status, $statuses);
        if ($order->status === 'cancelled') {
            $currentStatusIndex = -1; // special handling
        }

        return view('orders.show', compact('order', 'statuses', 'currentStatusIndex'));
    }
}

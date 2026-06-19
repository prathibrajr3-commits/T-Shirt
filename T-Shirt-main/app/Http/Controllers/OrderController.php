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
}

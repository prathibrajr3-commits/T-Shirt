<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Calculate sales metrics
        $totalSales = Order::where('payment_status', 'completed')->sum('total_amount');
        $ordersCount = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        
        // 2. Out of stock & low stock warning
        $lowStockProducts = Product::where('stock', '<', 10)->get();

        // 3. Recent Reviews
        $recentReviews = Review::with(['user', 'product'])->latest()->take(5)->get();

        // 4. Recent Orders
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalSales',
            'ordersCount',
            'pendingOrders',
            'processingOrders',
            'lowStockProducts',
            'recentReviews',
            'recentOrders'
        ));
    }
}

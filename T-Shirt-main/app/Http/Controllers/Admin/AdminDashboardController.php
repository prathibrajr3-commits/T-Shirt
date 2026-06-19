<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Calculate overall KPI metrics (static totals)
        $totalSales = Order::where('payment_status', 'completed')->sum('total_amount');
        $ordersCount = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        // 2. Dynamic Performance Reports (Date Filtered)
        $range = $request->get('range', 'this_month');
        $startDate = match ($range) {
            'today' => now()->startOfDay(),
            '7days' => now()->subDays(6)->startOfDay(),
            '30days' => now()->subDays(29)->startOfDay(),
            'this_month' => now()->startOfMonth(),
            'this_year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
        $endDate = now()->endOfDay();

        // Filtered counts
        $reportRevenue = Order::where('payment_status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        
        $reportOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $reportDelivered = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $reportCancelled = Order::where('status', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $completedOrdersCount = Order::where('payment_status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $reportAOV = $completedOrdersCount > 0 ? ($reportRevenue / $completedOrdersCount) : 0;

        // 3. Top Selling Products
        $topSellingProducts = \App\Models\OrderItem::select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'))
            ->with(['product.category'])
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 4. Out of stock & low stock warning
        $lowStockProducts = Product::where('stock', '<', 10)->get();

        // 5. Recent Reviews
        $recentReviews = Review::with(['user', 'product'])->latest()->take(5)->get();

        // 6. Recent Orders
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalSales',
            'ordersCount',
            'pendingOrders',
            'processingOrders',
            'deliveredOrders',
            'cancelledOrders',
            'range',
            'reportRevenue',
            'reportOrders',
            'reportDelivered',
            'reportCancelled',
            'reportAOV',
            'topSellingProducts',
            'lowStockProducts',
            'recentReviews',
            'recentOrders'
        ));
    }
}

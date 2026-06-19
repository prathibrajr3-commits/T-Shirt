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

        // Cancellation Analytics breakdown
        $totalCancelledOverall = Order::where('status', 'cancelled')->count();
        $totalCancelledToday = Order::where('status', 'cancelled')->whereDate('cancelled_at', today())->count();
        $totalCancelledThisMonth = Order::where('status', 'cancelled')->whereMonth('cancelled_at', now()->month)->whereYear('cancelled_at', now()->year)->count();

        $customerCancelledOverall = Order::where('status', 'cancelled')->where('cancelled_by', 'customer')->count();
        $customerCancelledToday = Order::where('status', 'cancelled')->where('cancelled_by', 'customer')->whereDate('cancelled_at', today())->count();
        $customerCancelledThisMonth = Order::where('status', 'cancelled')->where('cancelled_by', 'customer')->whereMonth('cancelled_at', now()->month)->whereYear('cancelled_at', now()->year)->count();

        $adminCancelledOverall = Order::where('status', 'cancelled')->where('cancelled_by', 'admin')->count();
        $adminCancelledToday = Order::where('status', 'cancelled')->where('cancelled_by', 'admin')->whereDate('cancelled_at', today())->count();
        $adminCancelledThisMonth = Order::where('status', 'cancelled')->where('cancelled_by', 'admin')->whereMonth('cancelled_at', now()->month)->whereYear('cancelled_at', now()->year)->count();

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

        // 7. Returns and Refunds Metrics
        $pendingReturns = \App\Models\ReturnRequest::where('status', 'pending')->count();
        $approvedReturns = \App\Models\ReturnRequest::where('status', 'approved')->count();
        $completedRefunds = \App\Models\ReturnRequest::where('status', 'completed')->count();

        $totalReturnRequests = \App\Models\ReturnRequest::count();
        $deliveredOrdersCount = \App\Models\Order::whereNotNull('delivered_at')->count();
        $returnRate = $deliveredOrdersCount > 0 ? ($totalReturnRequests / $deliveredOrdersCount) * 100 : 0.0;

        // 8. Coupon and Marketing Metrics
        $activeCoupons = \App\Models\Coupon::where('is_active', true)->count();
        $totalCouponUsage = \App\Models\CouponUsage::count();
        $discountGiven = \App\Models\Order::sum('discount_amount');

        $topPerformingCouponModel = \App\Models\Coupon::orderBy('usage_count', 'desc')->first();
        $topPerformingCoupon = $topPerformingCouponModel && $topPerformingCouponModel->usage_count > 0
            ? $topPerformingCouponModel->code
            : 'None';

        return view('admin.dashboard', compact(
            'totalSales',
            'ordersCount',
            'pendingOrders',
            'processingOrders',
            'deliveredOrders',
            'cancelledOrders',
            'pendingReturns',
            'approvedReturns',
            'completedRefunds',
            'returnRate',
            'activeCoupons',
            'totalCouponUsage',
            'discountGiven',
            'topPerformingCoupon',
            'totalCancelledOverall',
            'totalCancelledToday',
            'totalCancelledThisMonth',
            'customerCancelledOverall',
            'customerCancelledToday',
            'customerCancelledThisMonth',
            'adminCancelledOverall',
            'adminCancelledToday',
            'adminCancelledThisMonth',
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

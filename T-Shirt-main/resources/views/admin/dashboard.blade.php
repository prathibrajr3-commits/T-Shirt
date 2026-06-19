@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Dashboard</h1>
    <span class="text-secondary small">{{ date('F d, Y') }}</span>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4 col-sm-6 col-lg-2">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Total Revenue</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-success">₹{{ number_format($totalSales, 0) }}</h4>
                <i class="fa-solid fa-circle-dollar-to-slot text-success fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-lg-2">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Total Orders</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-white">{{ $ordersCount }}</h4>
                <i class="fa-solid fa-boxes-stacked text-primary fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-lg-2">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Pending Orders</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-warning">{{ $pendingOrders }}</h4>
                <i class="fa-solid fa-clock text-warning fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-lg-2">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Processing Orders</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-info">{{ $processingOrders }}</h4>
                <i class="fa-solid fa-arrows-spin text-info fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-lg-2">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Delivered Orders</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-success">{{ $deliveredOrders }}</h4>
                <i class="fa-solid fa-truck-ramp-box text-success fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-lg-2">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Cancelled Orders</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-danger">{{ $cancelledOrders }}</h4>
                <i class="fa-solid fa-circle-xmark text-danger fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<!-- Performance Reports Section -->
<div class="glass-panel p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h4 class="brand-font mb-1 text-white"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Fulfillment & Revenue Reports</h4>
            <p class="text-secondary small mb-0">Select a date range to filter sales performance statistics.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <form action="{{ route('admin.dashboard') }}" method="GET" id="rangeForm" class="d-flex align-items-center gap-2">
                <label for="range" class="text-secondary small me-1">Range:</label>
                <select name="range" id="range" class="form-select form-control-custom py-1" onchange="document.getElementById('rangeForm').submit();" style="width: auto;">
                    <option value="today" {{ $range === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="7days" {{ $range === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30days" {{ $range === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="this_month" {{ $range === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="this_year" {{ $range === 'this_year' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Filtered Metric Widgets -->
    <div class="row g-3">
        <div class="col-md-3 col-sm-6">
            <div class="p-3 rounded bg-dark bg-opacity-50 border border-secondary border-opacity-10">
                <span class="text-secondary small d-block mb-1">Period Revenue</span>
                <strong class="text-success fs-4">₹{{ number_format($reportRevenue, 2) }}</strong>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="p-3 rounded bg-dark bg-opacity-50 border border-secondary border-opacity-10">
                <span class="text-secondary small d-block mb-1">Period Orders</span>
                <strong class="text-white fs-4">{{ $reportOrders }}</strong>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="p-3 rounded bg-dark bg-opacity-50 border border-secondary border-opacity-10">
                <span class="text-secondary small d-block mb-1">Delivered</span>
                <strong class="text-success fs-4">{{ $reportDelivered }}</strong>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="p-3 rounded bg-dark bg-opacity-50 border border-secondary border-opacity-10">
                <span class="text-secondary small d-block mb-1">Cancelled</span>
                <strong class="text-danger fs-4">{{ $reportCancelled }}</strong>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="p-3 rounded bg-dark bg-opacity-50 border border-secondary border-opacity-10">
                <span class="text-secondary small d-block mb-1">Avg Order Value</span>
                <strong class="text-info fs-4">₹{{ number_format($reportAOV, 2) }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Recent Orders -->
    <div class="col-lg-6">
        <div class="glass-panel p-4 h-100">
            <h4 class="brand-font mb-4 text-white">Recent Orders</h4>
            @if($recentOrders->isEmpty())
                <p class="text-secondary py-4 text-center">No orders placed yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td class="fw-bold text-white">{{ $order->order_number }}</td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>
                                        <span class="badge {{ $order->statusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                    </td>
                                    <td class="text-end fw-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-premium-outline btn-sm py-0 px-2">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-lg-6">
        <div class="glass-panel p-4 h-100">
            <h4 class="brand-font mb-4 text-white"><i class="fa-solid fa-fire me-2 text-warning"></i> Top Selling Products</h4>
            @if($topSellingProducts->isEmpty())
                <p class="text-secondary py-4 text-center">No products sold yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Units Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSellingProducts as $item)
                                @if($item->product)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset($item->product->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;" alt="">
                                                <strong class="text-white small">{{ $item->product->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $item->product->category->name ?? 'Uncategorized' }}</td>
                                        <td class="text-end fw-bold text-success">{{ $item->total_qty }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Low Stock warning -->
    <div class="col-lg-6">
        <div class="glass-panel p-4 h-100">
            <h4 class="brand-font mb-4 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Low Stock Warnings</h4>
            @if($lowStockProducts->isEmpty())
                <p class="text-success py-4 text-center"><i class="fa-solid fa-circle-check me-2"></i> All products are well-stocked.</p>
            @else
                <div class="pe-1" style="max-height: 250px; overflow-y: auto;">
                    @foreach($lowStockProducts as $prod)
                        <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded bg-danger bg-opacity-10 border border-danger border-opacity-25">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset($prod->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="">
                                <div>
                                    <strong class="text-white small d-block">{{ $prod->name }}</strong>
                                    <span class="text-secondary small">{{ $prod->category->name }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger fs-6">{{ $prod->stock }} left</span>
                                <a href="{{ route('admin.products.edit', $prod->id) }}" class="text-primary d-block small mt-1 text-decoration-none"><i class="fa-solid fa-plus me-1"></i>Restock</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="col-lg-6">
        <div class="glass-panel p-4 mb-0 h-100">
            <h4 class="brand-font mb-4 text-white">Recent Customer Reviews</h4>
            @if($recentReviews->isEmpty())
                <p class="text-secondary py-4 text-center">No reviews submitted yet.</p>
            @else
                <div class="pe-1" style="max-height: 250px; overflow-y: auto;">
                    @foreach($recentReviews as $rev)
                        <div class="review-card p-3 mb-2 rounded bg-dark bg-opacity-25 border border-secondary border-opacity-10">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-white small">{{ $rev->user->name }}</strong>
                                <span class="text-secondary small" style="font-size: 0.75rem;">{{ $rev->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="small text-secondary mb-2" style="font-size: 0.8rem;">
                                Product: <span class="text-primary fw-bold">{{ $rev->product->name }}</span>
                            </div>
                            <div class="text-warning small mb-2" style="font-size: 0.8rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= $rev->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                @endfor
                            </div>
                            <p class="text-secondary mb-0 small" style="line-height: 1.4; font-size: 0.8rem;">
                                "{{ Str::limit($rev->comment, 150) }}"
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

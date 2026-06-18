@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Dashboard</h1>
    <span class="text-secondary small">{{ date('F d, Y') }}</span>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="admin-metric-card">
            <span class="text-secondary small d-block mb-1">Total Sales Revenue</span>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="brand-font mb-0 text-success">₹{{ number_format($totalSales, 2) }}</h3>
                <i class="fa-solid fa-circle-dollar-to-slot text-success fs-3 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="admin-metric-card">
            <span class="text-secondary small d-block mb-1">Total Orders</span>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="brand-font mb-0 text-white">{{ $ordersCount }}</h3>
                <i class="fa-solid fa-boxes-stacked text-primary fs-3 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="admin-metric-card">
            <span class="text-secondary small d-block mb-1">Pending Orders</span>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="brand-font mb-0 text-warning">{{ $pendingOrders }}</h3>
                <i class="fa-solid fa-clock text-warning fs-3 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="admin-metric-card">
            <span class="text-secondary small d-block mb-1">Processing Orders</span>
            <div class="d-flex align-items-center justify-content-between">
                <h3 class="brand-font mb-0 text-info">{{ $processingOrders }}</h3>
                <i class="fa-solid fa-arrows-spin text-info fs-3 opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Recent Orders -->
    <div class="col-lg-7">
        <div class="glass-panel p-4 h-100">
            <h4 class="brand-font mb-4">Recent Orders</h4>
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
                                        @if($order->status === 'pending')
                                            <span class="badge bg-warning text-dark">{{ $order->status }}</span>
                                        @elseif($order->status === 'processing')
                                            <span class="badge bg-info text-dark">{{ $order->status }}</span>
                                        @elseif($order->status === 'shipped')
                                            <span class="badge bg-primary">{{ $order->status }}</span>
                                        @elseif($order->status === 'delivered')
                                            <span class="badge bg-success">{{ $order->status }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $order->status }}</span>
                                        @endif
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

    <!-- Low Stock warning -->
    <div class="col-lg-5">
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
</div>

<!-- Recent Reviews -->
<div class="glass-panel p-4 mb-4">
    <h4 class="brand-font mb-4">Recent Customer Reviews</h4>
    @if($recentReviews->isEmpty())
        <p class="text-secondary py-4 text-center">No reviews submitted yet.</p>
    @else
        <div class="row g-3">
            @foreach($recentReviews as $rev)
                <div class="col-md-6 col-lg-4">
                    <div class="review-card mb-0 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-white">{{ $rev->user->name }}</strong>
                            <span class="text-secondary small">{{ $rev->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="small text-secondary mb-2">
                            Product: <span class="text-primary fw-bold">{{ $rev->product->name }}</span>
                        </div>
                        <div class="text-warning small mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $rev->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                            @endfor
                        </div>
                        <p class="text-secondary mb-0 small" style="line-height: 1.4;">
                            "{{ Str::limit($rev->comment, 150) }}"
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

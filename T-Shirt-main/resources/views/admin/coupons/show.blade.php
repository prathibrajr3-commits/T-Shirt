@extends('layouts.admin')

@section('title', 'Coupon Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="brand-font h2">Coupon Report: <span class="text-warning">{{ $coupon->code }}</span></h1>
        <p class="text-secondary small mb-0">Detailed usage statistics and customer redemptions.</p>
    </div>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-premium-outline"><i class="fa-solid fa-arrow-left me-2"></i> Back to Coupons</a>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Total Usage Count</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-white">
                    {{ $usageCount }}
                    <span class="text-secondary fs-6 fw-normal">/ {{ $coupon->usage_limit ?? '∞' }}</span>
                </h4>
                <i class="fa-solid fa-ticket text-info fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Revenue Generated</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-success">₹{{ number_format($revenueGenerated, 2) }}</h4>
                <i class="fa-solid fa-circle-dollar-to-slot text-success fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-metric-card p-3">
            <span class="text-secondary small d-block mb-1 text-truncate">Unique Customers</span>
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="brand-font mb-0 text-warning">{{ $uniqueCustomers }}</h4>
                <i class="fa-solid fa-users text-warning fs-4 opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Coupon Settings Summary -->
    <div class="col-lg-4">
        <div class="glass-panel p-4 h-100">
            <h4 class="brand-font mb-4 text-white">Coupon Details</h4>
            
            <div class="mb-3">
                <span class="text-secondary small d-block">Status</span>
                @if($coupon->is_active)
                    <span class="badge bg-success badge-custom">Active</span>
                @else
                    <span class="badge bg-secondary badge-custom text-secondary border border-secondary border-opacity-25 bg-dark">Inactive</span>
                @endif
            </div>

            <div class="mb-3">
                <span class="text-secondary small d-block">Discount Value</span>
                <strong class="text-white fs-5">
                    @if($coupon->discount_type === 'percentage')
                        {{ number_format($coupon->discount_value, 0) }}%
                    @else
                        ₹{{ number_format($coupon->discount_value, 2) }}
                    @endif
                </strong>
                <span class="text-secondary small">({{ ucfirst($coupon->discount_type) }})</span>
            </div>

            <div class="mb-3">
                <span class="text-secondary small d-block">Minimum Order Amount</span>
                <strong class="text-white">₹{{ number_format($coupon->minimum_order_amount, 2) }}</strong>
            </div>

            @if($coupon->discount_type === 'percentage')
                <div class="mb-3">
                    <span class="text-secondary small d-block">Max Discount Cap</span>
                    <strong class="text-white">
                        {{ $coupon->maximum_discount_amount ? '₹' . number_format($coupon->maximum_discount_amount, 2) : 'No Cap' }}
                    </strong>
                </div>
            @endif

            <div class="mb-3">
                <span class="text-secondary small d-block">Customer Usage Limit</span>
                <strong class="text-white">{{ $coupon->usage_per_customer }} time(s) per customer</strong>
            </div>

            <div class="mb-3">
                <span class="text-secondary small d-block">Validity Period</span>
                <strong class="text-white small">
                    @if($coupon->start_date && $coupon->end_date)
                        {{ $coupon->start_date->format('M d, Y H:i') }} - {{ $coupon->end_date->format('M d, Y H:i') }}
                    @elseif($coupon->start_date)
                        Starts: {{ $coupon->start_date->format('M d, Y H:i') }}
                    @elseif($coupon->end_date)
                        Expires: {{ $coupon->end_date->format('M d, Y H:i') }}
                    @else
                        Always Valid (No Date Range)
                    @endif
                </strong>
            </div>

            @if($coupon->description)
                <div class="mb-0">
                    <span class="text-secondary small d-block">Description</span>
                    <p class="text-secondary small mb-0" style="line-height: 1.4;">{{ $coupon->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Usage History Table -->
    <div class="col-lg-8">
        <div class="glass-panel p-4 h-100">
            <h4 class="brand-font mb-4 text-white">Usage Redemptions</h4>
            
            @if($usages->isEmpty())
                <p class="text-secondary py-5 text-center"><i class="fa-solid fa-receipt fs-2 mb-3 d-block opacity-50 text-secondary"></i> This coupon has not been used yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Order Total</th>
                                <th class="text-center">Used At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usages as $usage)
                                <tr>
                                    <td>
                                        @if($usage->order)
                                            <a href="{{ route('admin.orders.show', $usage->order->id) }}" class="fw-bold text-premium text-decoration-none">
                                                {{ $usage->order->order_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($usage->user)
                                            <strong class="text-white">{{ $usage->user->name }}</strong>
                                            <span class="text-secondary d-block small" style="font-size: 0.75rem;">{{ $usage->user->email }}</span>
                                        @else
                                            <span class="text-muted">Deleted User</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-danger">-₹{{ number_format($usage->discount_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-white">
                                        @if($usage->order)
                                            ₹{{ number_format($usage->order->total_amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center text-secondary small">
                                        {{ $usage->used_at ? $usage->used_at->format('M d, Y H:i') : $usage->created_at->format('M d, Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $usages->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

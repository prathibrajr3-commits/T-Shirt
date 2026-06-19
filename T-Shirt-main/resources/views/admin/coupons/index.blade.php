@extends('layouts.admin')

@section('title', 'Manage Coupons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Coupons & Discounts</h1>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-premium"><i class="fa-solid fa-plus me-1"></i> Add Coupon</a>
</div>

<div class="glass-panel p-4">
    <h4 class="brand-font mb-4">Promo Coupons</h4>

    <!-- Search & Filter Bar -->
    <form action="{{ route('admin.coupons.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="search" class="form-label form-label-custom text-secondary small">Search Code</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" id="search" class="form-control form-control-custom" value="{{ request('search') }}" placeholder="Coupon code...">
            </div>
        </div>
        
        <div class="col-md-3">
            <label for="status" class="form-label form-label-custom text-secondary small">Status Filter</label>
            <select name="status" id="status" class="form-select form-control-custom">
                <option value="">All Coupons</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2 ms-auto">
            <button type="submit" class="btn btn-premium w-100 py-2"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-premium-outline py-2"><i class="fa-solid fa-rotate-left"></i></a>
            @endif
        </div>
    </form>

    @if($coupons->isEmpty())
        <p class="text-secondary py-5 text-center"><i class="fa-solid fa-ticket fs-2 mb-3 d-block opacity-50 text-warning"></i> No coupons found.</p>
    @else
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th class="text-end">Min Order</th>
                        <th class="text-center">Usage Count</th>
                        <th class="text-center">Limit</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $coupon)
                        <tr>
                            <td class="fw-bold text-white">{{ $coupon->code }}</td>
                            <td class="text-uppercase">{{ $coupon->discount_type }}</td>
                            <td>
                                @if($coupon->discount_type === 'percentage')
                                    {{ number_format($coupon->discount_value, 0) }}%
                                @else
                                    ₹{{ number_format($coupon->discount_value, 2) }}
                                @endif
                            </td>
                            <td class="text-end">₹{{ number_format($coupon->minimum_order_amount, 2) }}</td>
                            <td class="text-center">{{ $coupon->usage_count }}</td>
                            <td class="text-center">{{ $coupon->usage_limit ?? 'Unlimited' }}</td>
                            <td class="text-center">
                                @if($coupon->is_active)
                                    <span class="badge bg-success badge-custom">Active</span>
                                @else
                                    <span class="badge bg-secondary badge-custom text-secondary border border-secondary border-opacity-25 bg-dark">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.coupons.show', $coupon->id) }}" class="btn btn-premium-outline btn-sm py-1 px-2" title="View Report">
                                        <i class="fa-solid fa-chart-line"></i>
                                    </a>
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-premium btn-sm py-1 px-2" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coupon?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm py-1 px-2" style="background-color: var(--danger-color); border: none;" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $coupons->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection

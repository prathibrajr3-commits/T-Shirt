@extends('layouts.admin')

@section('title', 'Create Coupon')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Create Coupon</h1>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-premium-outline"><i class="fa-solid fa-arrow-left me-2"></i> Back to Coupons</a>
</div>

@if ($errors->any())
    <div class="alert alert-custom-danger mb-4">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="glass-panel p-4">
    <form action="{{ route('admin.coupons.store') }}" method="POST">
        @csrf
        
        <div class="row g-3">
            <!-- Coupon Code -->
            <div class="col-md-6 mb-3">
                <label for="code" class="form-label form-label-custom">Coupon Code <span class="text-danger">*</span></label>
                <input type="text" name="code" id="code" class="form-control form-control-custom" value="{{ old('code') }}" required placeholder="e.g. STREETWEAR20">
            </div>

            <!-- Status -->
            <div class="col-md-6 mb-3">
                <label for="is_active" class="form-label form-label-custom">Status <span class="text-danger">*</span></label>
                <select name="is_active" id="is_active" class="form-select form-control-custom" required>
                    <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Description -->
            <div class="col-12 mb-3">
                <label for="description" class="form-label form-label-custom">Description (Optional)</label>
                <textarea name="description" id="description" class="form-control form-control-custom" rows="2" placeholder="Brief details about the discount offer...">{{ old('description') }}</textarea>
            </div>

            <!-- Discount Type -->
            <div class="col-md-6 mb-3">
                <label for="discount_type" class="form-label form-label-custom">Discount Type <span class="text-danger">*</span></label>
                <select name="discount_type" id="discount_type" class="form-select form-control-custom" required>
                    <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                </select>
            </div>

            <!-- Discount Value -->
            <div class="col-md-6 mb-3">
                <label for="discount_value" class="form-label form-label-custom">Discount Value <span class="text-danger">*</span></label>
                <input type="number" name="discount_value" id="discount_value" step="0.01" min="0.01" class="form-control form-control-custom" value="{{ old('discount_value') }}" required placeholder="e.g. 15 for 15% or 150 for ₹150">
            </div>

            <!-- Minimum Order Amount -->
            <div class="col-md-6 mb-3">
                <label for="minimum_order_amount" class="form-label form-label-custom">Minimum Order Amount (₹) <span class="text-danger">*</span></label>
                <input type="number" name="minimum_order_amount" id="minimum_order_amount" step="0.01" min="0" class="form-control form-control-custom" value="{{ old('minimum_order_amount', '0.00') }}" required placeholder="e.g. 500">
            </div>

            <!-- Maximum Discount Amount -->
            <div class="col-md-6 mb-3">
                <label for="maximum_discount_amount" class="form-label form-label-custom">Max Discount Amount (Optional, ₹)</label>
                <input type="number" name="maximum_discount_amount" id="maximum_discount_amount" step="0.01" min="0" class="form-control form-control-custom" value="{{ old('maximum_discount_amount') }}" placeholder="For percentage coupons only">
            </div>

            <!-- Usage Limit -->
            <div class="col-md-6 mb-3">
                <label for="usage_limit" class="form-label form-label-custom">Total Usage Limit (Optional)</label>
                <input type="number" name="usage_limit" id="usage_limit" min="1" class="form-control form-control-custom" value="{{ old('usage_limit') }}" placeholder="Leave blank for unlimited">
            </div>

            <!-- Usage Per Customer -->
            <div class="col-md-6 mb-3">
                <label for="usage_per_customer" class="form-label form-label-custom">Usage Limit Per Customer <span class="text-danger">*</span></label>
                <input type="number" name="usage_per_customer" id="usage_per_customer" min="1" class="form-control form-control-custom" value="{{ old('usage_per_customer', '1') }}" required>
            </div>

            <!-- Start Date -->
            <div class="col-md-6 mb-3">
                <label for="start_date" class="form-label form-label-custom">Start Date & Time (Optional)</label>
                <input type="datetime-local" name="start_date" id="start_date" class="form-control form-control-custom" value="{{ old('start_date') }}">
            </div>

            <!-- End Date -->
            <div class="col-md-6 mb-3">
                <label for="end_date" class="form-label form-label-custom">End Date & Time (Optional)</label>
                <input type="datetime-local" name="end_date" id="end_date" class="form-control form-control-custom" value="{{ old('end_date') }}">
            </div>
        </div>

        <div class="mt-4 pt-2 border-top border-secondary border-opacity-10 text-end">
            <button type="submit" class="btn btn-premium py-2 px-4">Create Coupon</button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<h2 class="brand-font mb-4">Secure Checkout</h2>

@if ($errors->any())
    <div class="alert alert-custom-danger mb-4">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(request()->has('payment') && request('payment') === 'failed')
    <div class="alert alert-custom-danger d-flex align-items-center alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <div>Payment Failed: {{ request('reason', 'Transaction was unsuccessful. Please try again.') }}</div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(request()->has('payment') && request('payment') === 'cancelled')
    <div class="alert alert-custom-danger d-flex align-items-center alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-circle-xmark me-2"></i>
        <div>Payment Cancelled: You closed the payment gateway window.</div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
    @csrf
    <div class="row g-4">
        <!-- Billing/Shipping Information -->
        <div class="col-lg-8">
            <div class="glass-panel p-4 mb-4">
                <h4 class="brand-font mb-4">1. Delivery Information</h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label form-label-custom">Full Name</label>
                        <input type="text" class="form-control form-control-custom" value="{{ $user->name }}" readonly disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label form-label-custom">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control form-control-custom" value="{{ old('phone', $user->phone) }}" required placeholder="+1 (555) 000-0000">
                    </div>
                </div>

                <div class="mb-0">
                    <label for="shipping_address" class="form-label form-label-custom">Shipping Address</label>
                    <textarea name="shipping_address" id="shipping_address" class="form-control form-control-custom" rows="3" required placeholder="Street address, Apt/Suite, City, Postal Code, Country">{{ old('shipping_address', $user->address) }}</textarea>
                </div>
            </div>

            <div class="glass-panel p-4">
                <h4 class="brand-font mb-4">2. Payment Method</h4>
                
                <!-- COD Option -->
                <div class="form-check p-3 rounded mb-3 bg-dark bg-opacity-25 border border-secondary border-opacity-25">
                    <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_cod" value="cod" checked required>
                    <label class="form-check-label text-white d-flex align-items-center" for="pay_cod">
                        <i class="fa-solid fa-hand-holding-dollar me-2 text-primary fs-5"></i>
                        <div>
                            <span class="fw-bold">Cash on Delivery (COD)</span>
                            <div class="text-secondary small">Pay with cash when your package is delivered.</div>
                        </div>
                    </label>
                </div>

                <!-- Online Payment (Razorpay) -->
                <div class="form-check p-3 rounded mb-4 bg-dark bg-opacity-25 border border-secondary border-opacity-25">
                    <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_razorpay" value="razorpay" required>
                    <label class="form-check-label text-white d-flex align-items-center" for="pay_razorpay">
                        <i class="fa-solid fa-credit-card me-2 text-primary fs-5"></i>
                        <div>
                            <span class="fw-bold">Online Payment (Razorpay)</span>
                            <div class="text-secondary small">Pay securely online using Credit Card, Debit Card, Netbanking, UPI, or Wallet.</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="glass-panel p-4 sticky-md-top" style="top: 100px;">
                <h4 class="brand-font mb-4">Order Summary</h4>
                
                <div class="mb-4" style="max-height: 250px; overflow-y: auto;">
                    @foreach($cart as $item)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset($item['image_path'] ?? 'images/tshirts/cyberpunk.png') }}" class="cart-item-img me-2" style="width: 45px; height: 45px;" alt="{{ $item['name'] }}">
                                <div>
                                    <div class="text-white small fw-bold text-truncate" style="max-width: 140px;">{{ $item['name'] }}</div>
                                    <div class="text-secondary small">{{ $item['size'] }} / {{ $item['color'] }} x {{ $item['quantity'] }}</div>
                                </div>
                            </div>
                            <span class="text-white small">₹{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <hr class="border-secondary opacity-25 my-3">
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Subtotal</span>
                    <span class="fw-bold">₹{{ number_format($total, 2) }}</span>
                </div>
                
                @php
                    $shipping = $shippingSettings->calculateShipping($total);
                @endphp

                @if($shippingSettings->is_active)
                <div class="d-flex justify-content-between mb-3"
                     style="color: {{ $shippingSettings->text_color }};">
                    <span class="text-secondary d-flex align-items-center gap-2">
                        <i class="{{ $shippingSettings->icon_class }}"></i>
                        Shipping
                    </span>
                    <span class="fw-bold">{{ $shipping == 0 ? 'FREE' : '₹' . number_format($shipping, 2) }}</span>
                </div>

                @if($shippingSettings->show_free_shipping_promo && $shipping > 0 && $shippingSettings->shipping_type === 'free_above_threshold')
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        {!! $shippingSettings->buildPromoMessage($total) !!}
                    </div>
                @elseif($shippingSettings->show_free_shipping_promo && $shipping == 0 && $shippingSettings->shipping_type === 'free_above_threshold')
                    <div class="alert alert-success py-2 px-3 small mb-3">
                        <i class="fa-solid fa-circle-check me-1"></i>
                        {{ $shippingSettings->free_shipping_message }}
                    </div>
                @endif
                @endif

                @if($discount > 0)
                <div class="d-flex justify-content-between mb-3 text-success">
                    <span class="text-secondary text-success">Discount</span>
                    <span class="fw-bold">-₹{{ number_format($discount, 2) }}</span>
                </div>
                @endif

                <!-- Coupon Section -->
                <div class="my-3 border-top border-secondary border-opacity-10 pt-3">
                    @if($couponCode)
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
                            <div>
                                <span class="text-secondary small d-block" style="font-size: 0.75rem;">Applied Coupon</span>
                                <strong class="text-success small">{{ $couponCode }}</strong>
                            </div>
                            <button type="submit" form="coupon-remove-form" class="btn btn-sm btn-link text-danger p-0 border-0 text-decoration-none small">Remove</button>
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="coupon_code" class="form-label form-label-custom small mb-1">Apply Coupon</label>
                            <div class="input-group">
                                <input type="text" name="code" id="coupon_code" form="coupon-apply-form" class="form-control form-control-custom py-1" style="height: auto; font-size: 0.85rem;" placeholder="Coupon code..." required>
                                <button type="submit" form="coupon-apply-form" class="btn btn-premium btn-sm px-3" style="font-size: 0.85rem;">Apply</button>
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="border-secondary opacity-25 my-3">

                <div class="d-flex justify-content-between mb-4">
                    <span class="text-white fs-5 font-weight-bold">Total Amount</span>
                    <span class="text-success fs-4 fw-bold">₹{{ number_format(max(0, $total - $discount) + $shipping, 2) }}</span>
                </div>

                <button type="submit" class="btn btn-premium w-100 py-3 mb-2">
                    Place Order <i class="fa-solid fa-shield-check ms-2"></i>
                </button>
                <a href="{{ route('cart.index') }}" class="btn btn-premium-outline w-100">
                    Back to Cart
                </a>
            </div>
        </div>
    </div>
</form>

<!-- External Coupon Forms (To avoid form nesting in HTML) -->
<form action="{{ route('coupon.apply') }}" method="POST" id="coupon-apply-form" style="display:none;">
    @csrf
</form>
<form action="{{ route('coupon.remove') }}" method="POST" id="coupon-remove-form" style="display:none;">
    @csrf
</form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checkout-form');
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = 'Processing... <i class="fa-solid fa-spinner fa-spin ms-2"></i>';
        });
    });
</script>
@endsection

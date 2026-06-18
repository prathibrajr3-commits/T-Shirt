@extends('layouts.app')

@section('title', 'Processing Payment')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center py-5">
    <div class="glass-panel p-5 text-center my-4" style="max-width: 500px; width: 100%;">
        <div class="spinner-border text-primary mb-4" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h3 class="brand-font mb-3 text-white">Initiating Secure Payment</h3>
        <p class="text-secondary mb-4">Please do not close this window or click back. We are opening the Razorpay payment gateway to process your order of <strong>₹{{ number_format($total_amount, 2) }}</strong>.</p>
        
        <button id="rzp-button" class="btn btn-premium px-4 py-2">Open Payment Window Manually</button>
    </div>
</div>

<!-- Hidden Success Form -->
<form id="payment-success-form" action="{{ route('checkout.razorpay-callback') }}" method="POST">
    @csrf
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <input type="hidden" name="shipping_address" value="{{ $shipping_address }}">
</form>

@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var options = {
            "key": "{{ $key_id }}",
            "amount": "{{ $amount }}",
            "currency": "INR",
            "name": "AURAWEAR",
            "description": "Premium Streetwear Order",
            "image": "https://cdn-icons-png.flaticon.com/512/825/825590.png",
            "order_id": "{{ $razorpay_order_id }}",
            "handler": function (response) {
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.getElementById('payment-success-form').submit();
            },
            "prefill": {
                "name": "{{ $user->name }}",
                "email": "{{ $user->email }}",
                "contact": "{{ $phone }}"
            },
            "theme": {
                "color": "#8b5cf6"
            },
            "modal": {
                "ondismiss": function() {
                    window.location.href = "{{ route('checkout.index') }}?payment=cancelled";
                }
            }
        };
        
        var rzp1 = new Razorpay(options);
        
        rzp1.on('payment.failed', function (response) {
            window.location.href = "{{ route('checkout.index') }}?payment=failed&reason=" + encodeURIComponent(response.error.description);
        });

        // Auto-open on load
        rzp1.open();

        // Fallback manual click button
        document.getElementById('rzp-button').onclick = function(e) {
            rzp1.open();
            e.preventDefault();
        };
    });
</script>
@endsection

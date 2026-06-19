@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<h2 class="brand-font mb-4">Shopping Bag</h2>

@if(empty($cart))
    <div class="glass-panel p-5 text-center my-4">
        <i class="fa-solid fa-bag-shopping fs-1 text-secondary mb-3"></i>
        <h4 class="brand-font">Your cart is empty</h4>
        <p class="text-secondary">Fill it with our premium streetwear collection!</p>
        <a href="{{ route('shop.list') }}" class="btn btn-premium mt-3">Browse Products</a>
    </div>
@else
    <div class="row g-4">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="glass-panel p-4">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Size / Color</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart as $key => $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($item['image_path'] ?? 'images/tshirts/cyberpunk.png') }}" class="cart-item-img me-3" alt="{{ $item['name'] }}">
                                            <div>
                                                <a href="{{ route('shop.show', $item['slug']) }}" class="text-decoration-none text-white fw-bold">
                                                    {{ $item['name'] }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary me-1">{{ $item['size'] }}</span>
                                        <span class="badge bg-secondary">{{ $item['color'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('cart.update') }}" method="POST" class="d-inline-flex align-items-center justify-content-center" style="max-width: 130px; margin: 0 auto;">
                                            @csrf
                                            <input type="hidden" name="key" value="{{ $key }}">
                                            <input type="number" name="quantity" class="form-control form-control-custom text-center py-1 px-2 me-2" value="{{ $item['quantity'] }}" min="1" style="font-size: 0.9rem;">
                                            <button type="submit" class="btn btn-premium btn-sm p-1"><i class="fa-solid fa-rotate"></i></button>
                                        </form>
                                    </td>
                                    <td class="text-end fw-bold">
                                        ₹{{ number_format($item['price'] * $item['quantity'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('cart.remove', $key) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-danger p-0 border-0" onclick="return confirm('Remove item?')">
                                                <i class="fa-solid fa-trash-can fs-5"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="glass-panel p-4 sticky-md-top" style="top: 100px;">
                <h4 class="brand-font mb-4">Summary</h4>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Subtotal</span>
                    <span class="fw-bold">₹{{ number_format($total, 2) }}</span>
                </div>
                
                @if($discount > 0)
                <div class="d-flex justify-content-between mb-3 text-success">
                    <span class="text-secondary text-success">Discount</span>
                    <span class="fw-bold">-₹{{ number_format($discount, 2) }}</span>
                </div>
                @endif
                
                @php
                    $shippingSettings = \App\Models\ShippingSetting::getSettings();
                    $shipping = $shippingSettings->calculateShipping($total);
                @endphp

                @if($shippingSettings->is_active)
                <div class="d-flex justify-content-between mb-3"
                     style="color: {{ $shippingSettings->text_color }}; border-radius: {{ $shippingSettings->border_radius }}; {{ $shippingSettings->background_color !== 'transparent' ? 'background:'.$shippingSettings->background_color.'; padding: 0.5rem 0.75rem;' : '' }}">
                    <span class="text-secondary d-flex align-items-center gap-2">
                        <i class="{{ $shippingSettings->icon_class }}"></i>
                        Estimated Shipping
                    </span>
                    <span class="fw-bold">{{ $shipping == 0 ? 'FREE' : '₹' . number_format($shipping, 2) }}</span>
                </div>

                @if($shippingSettings->show_free_shipping_promo)
                    @if($shipping > 0 && $shippingSettings->shipping_type === 'free_above_threshold')
                        <div class="alert alert-info py-2 small bg-opacity-10 text-info border-info mb-3">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            {!! $shippingSettings->buildPromoMessage($total) !!}
                        </div>
                    @elseif($shipping == 0 && $shippingSettings->shipping_type === 'free_above_threshold')
                        <div class="alert alert-success py-2 small mb-3">
                            <i class="fa-solid fa-circle-check me-2"></i>
                            {{ $shippingSettings->free_shipping_message }}
                        </div>
                    @endif
                @endif
                @endif

                <!-- Coupon Section -->
                <div class="my-3 border-top border-secondary border-opacity-10 pt-3">
                    @if($couponCode)
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
                            <div>
                                <span class="text-secondary small d-block" style="font-size: 0.75rem;">Applied Coupon</span>
                                <strong class="text-success small">{{ $couponCode }}</strong>
                            </div>
                            <form action="{{ route('coupon.remove') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 text-decoration-none small">Remove</button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('coupon.apply') }}" method="POST" class="mb-3">
                            @csrf
                            <label for="coupon_code" class="form-label form-label-custom small mb-1">Apply Coupon</label>
                            <div class="input-group">
                                <input type="text" name="code" id="coupon_code" class="form-control form-control-custom py-1" style="height: auto; font-size: 0.85rem;" placeholder="Coupon code..." required>
                                <button type="submit" class="btn btn-premium btn-sm px-3" style="font-size: 0.85rem;">Apply</button>
                            </div>
                        </form>
                    @endif
                </div>

                <hr class="border-secondary opacity-25 my-3">

                <div class="d-flex justify-content-between mb-4">
                    <span class="text-white fs-5 font-weight-bold">Total</span>
                    <span class="text-success fs-4 fw-bold">₹{{ number_format(max(0, $total - $discount) + $shipping, 2) }}</span>
                </div>

                <a href="{{ route('checkout.index') }}" class="btn btn-premium w-100 py-3 mb-2">
                    Proceed to Checkout <i class="fa-solid fa-credit-card ms-2"></i>
                </a>
                <a href="{{ route('shop.list') }}" class="btn btn-premium-outline w-100">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
@endif
@endsection

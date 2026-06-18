@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="row g-4">
    <!-- Main Tracking Details -->
    <div class="col-lg-8">
        <!-- Order header info -->
        <div class="glass-panel p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <div>
                    <h3 class="brand-font mb-1 text-white">Order Details</h3>
                    <p class="text-secondary mb-0">Order: <span class="fw-bold text-light">{{ $order->order_number }}</span> | Placed on {{ $order->created_at->format('F d, Y h:i A') }}</p>
                </div>
                <div class="mt-3 mt-md-0">
                    @if($order->status === 'cancelled')
                        <span class="badge bg-danger fs-6 px-3 py-2 badge-custom">Cancelled</span>
                    @else
                        <span class="badge bg-success fs-6 px-3 py-2 badge-custom">Status: {{ ucfirst($order->status) }}</span>
                    @endif
                </div>
            </div>

            <!-- Stepper tracking indicator -->
            @if($order->status !== 'cancelled')
                <div class="py-3 px-2">
                    <div class="stepper-wrapper">
                        @foreach($statuses as $index => $status)
                            @php
                                $class = '';
                                if ($index < $currentStatusIndex) {
                                    $class = 'completed';
                                } elseif ($index == $currentStatusIndex) {
                                    $class = 'active';
                                }
                            @endphp
                            <div class="stepper-item {{ $class }}">
                                <div class="step-counter">
                                    @if($index < $currentStatusIndex)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </div>
                                <div class="step-name brand-font text-uppercase">{{ ucfirst($status) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="alert alert-danger mb-0 py-3">
                    <i class="fa-solid fa-circle-xmark me-2"></i> This order has been cancelled. If you believe this is an error, please contact customer support.
                </div>
            @endif

            <!-- Tracking number block if shipped -->
            @if($order->tracking_number && ($order->status === 'shipped' || $order->status === 'delivered'))
                <div class="mt-4 p-3 rounded border border-primary border-opacity-25 bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small d-block">Carrier Tracking Number</span>
                        <strong class="text-white fs-5">{{ $order->tracking_number }}</strong>
                    </div>
                    <span class="badge bg-primary-custom text-primary"><i class="fa-solid fa-truck me-2"></i> Active Transit</span>
                </div>
            @endif
        </div>

        <!-- Ordered Items list -->
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4">Items Ordered</h4>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Size / Color</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset($item->product->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="cart-item-img me-3" style="width: 50px; height: 50px;" alt="{{ $item->product->name }}">
                                        <div>
                                            <a href="{{ route('shop.show', $item->product->slug) }}" class="text-decoration-none text-white fw-bold small">
                                                {{ $item->product->name }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary me-1">{{ $item->size }}</span>
                                    <span class="badge bg-secondary">{{ $item->color }}</span>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-bold text-white">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Summary & Shipping Address -->
    <div class="col-lg-4">
        <!-- Billing summary -->
        <div class="glass-panel p-4 mb-4">
            <h4 class="brand-font mb-4">Billing Summary</h4>
            
            <div class="d-flex justify-content-between mb-2">
                <span class="text-secondary">Payment Method</span>
                <span class="fw-bold text-white text-uppercase">{{ $order->payment_method }}</span>
            </div>
            
            <div class="d-flex justify-content-between mb-3">
                <span class="text-secondary">Payment Status</span>
                <span class="fw-bold text-white text-uppercase">
                    @if($order->payment_status === 'completed')
                        <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> Completed</span>
                    @elseif($order->payment_status === 'pending')
                        <span class="text-warning"><i class="fa-solid fa-clock me-1"></i> Pending</span>
                    @else
                        <span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Failed</span>
                    @endif
                </span>
            </div>

            <hr class="border-secondary opacity-25 my-3">

            <div class="d-flex justify-content-between">
                <span class="text-white fs-5 font-weight-bold">Paid Total</span>
                <span class="text-success fs-4 fw-bold">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Shipping Address details -->
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4">Delivery Address</h4>
            
            <div class="mb-3">
                <span class="text-secondary small d-block">Recipient</span>
                <strong class="text-white">{{ $order->user->name }}</strong>
            </div>

            <div class="mb-3">
                <span class="text-secondary small d-block">Contact Phone</span>
                <span class="text-white">{{ $order->phone }}</span>
            </div>

            <div class="mb-0">
                <span class="text-secondary small d-block">Shipping Address</span>
                <span class="text-white" style="white-space: pre-line;">{{ $order->shipping_address }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

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
                    <p class="text-secondary mb-0">Order: <span class="fw-bold text-light">{{ $order->order_number }}</span> | Placed on {{ $order->created_at->timezone(config('app.timezone'))->format('F d, Y h:i A') }}</p>
                </div>
                <div class="mt-3 mt-md-0">
                    @if($order->status === 'cancelled')
                        <span class="badge bg-danger fs-6 px-3 py-2 badge-custom">Cancelled</span>
                    @elseif($order->status === 'refunded')
                        <span class="badge bg-dark fs-6 px-3 py-2 badge-custom text-secondary border border-secondary border-opacity-25">Refunded</span>
                    @else
                        <span class="badge bg-success fs-6 px-3 py-2 badge-custom">Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                    @endif
                </div>
            </div>

            <!-- Stepper tracking indicator -->
            @if($order->status !== 'cancelled' && $order->status !== 'refunded')
                <div class="py-3 px-2">
                    <div class="stepper-wrapper">
                        @php
                            $highestCompletedIndex = -1;
                            foreach($milestones as $index => $milestone) {
                                if ($milestone['completed']) {
                                    $highestCompletedIndex = $index;
                                }
                            }
                        @endphp
                        @foreach($milestones as $index => $milestone)
                            @php
                                $class = '';
                                if ($milestone['completed']) {
                                    if ($index === $highestCompletedIndex && $order->status !== 'delivered') {
                                        $class = 'active';
                                    } else {
                                        $class = 'completed';
                                    }
                                }
                            @endphp
                            <div class="stepper-item {{ $class }}">
                                <div class="step-counter">
                                    @if($milestone['completed'] && ($index < $highestCompletedIndex || $order->status === 'delivered'))
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </div>
                                <div class="step-name brand-font text-uppercase" style="font-size: 0.75rem;">{{ $milestone['name'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($order->status === 'refunded')
                <div class="alert alert-dark mb-0 py-3 border border-secondary border-opacity-25 bg-dark bg-opacity-50">
                    <i class="fa-solid fa-hand-holding-dollar me-2 text-warning"></i> This order has been refunded. If you have any questions, please contact customer support.
                </div>
            @else
                <div class="alert alert-danger mb-0 py-3">
                    <i class="fa-solid fa-circle-xmark me-2"></i> This order has been cancelled. If you believe this is an error, please contact customer support.
                </div>
            @endif

            <!-- Tracking number block if shipped -->
            @if(($order->tracking_number || $order->shipping_provider) && in_array($order->status, ['shipped', 'out_for_delivery', 'delivered']))
                <div class="mt-4 p-3 rounded border border-primary border-opacity-25 bg-primary bg-opacity-10 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <span class="text-secondary small d-block">Shipping Provider</span>
                        <strong class="text-white fs-6">{{ $order->shipping_provider ?? 'Standard Shipping' }}</strong>
                        @if($order->tracking_number)
                            <span class="text-secondary small d-block mt-1">Tracking Number: <strong class="text-light">{{ $order->tracking_number }}</strong></span>
                        @endif
                    </div>
                    <div>
                        @if($order->tracking_url)
                            <a href="{{ $order->tracking_url }}" target="_blank" class="btn btn-premium btn-sm py-2 px-3">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Track Shipment
                            </a>
                        @else
                            <span class="badge bg-primary"><i class="fa-solid fa-truck me-2"></i> In Transit</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Ordered Items list -->
        <div class="glass-panel p-4 mb-4">
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

        <!-- Order Activity History Timeline -->
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4 text-white"><i class="fa-solid fa-list-check me-2 text-primary"></i>Order Shipment History</h4>
            @if($order->histories->isEmpty())
                <p class="text-secondary small mb-0">No history details available yet.</p>
            @else
                <div class="timeline-logs">
                    @foreach($order->histories as $history)
                        <div class="d-flex mb-3 align-items-start border-start border-secondary border-opacity-25 ps-3 position-relative" style="margin-left: 10px;">
                            <div class="position-absolute bg-primary rounded-circle" style="width: 10px; height: 10px; left: -5px; top: 6px;"></div>
                            <div class="ms-2">
                                <strong class="text-white d-block small">
                                    {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                                </strong>
                                <span class="text-secondary small d-block mb-1" style="font-size: 0.8rem;">{{ $history->created_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</span>
                                @if($history->notes)
                                    <p class="text-light small mb-0">{{ $history->notes }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
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

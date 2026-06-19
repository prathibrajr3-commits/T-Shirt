@extends('layouts.admin')

@section('title', 'Manage Order ' . $order->order_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Manage Order</h1>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-premium-outline"><i class="fa-solid fa-arrow-left me-2"></i> Back to Orders</a>
</div>

<!-- Errors display -->
@if(session('error'))
    <div class="alert alert-custom-danger d-flex align-items-center alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Order items and billing details -->
    <div class="col-lg-8">
        <div class="glass-panel p-4 mb-4">
            <h4 class="brand-font mb-3 text-white">Order Summary: {{ $order->order_number }}</h4>
            <p class="text-secondary small">Placed by {{ $order->user->name }} ({{ $order->user->email }}) on {{ $order->created_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</p>

            <div class="table-responsive mt-3">
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
                                        <img src="{{ asset($item->product->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="">
                                        <div>
                                            <strong class="text-white small">{{ $item->product->name }}</strong>
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

            <hr class="border-secondary opacity-25 my-4">

            <div class="row justify-content-end">
                <div class="col-md-5 text-end">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal</span>
                        <span class="text-white fw-bold">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-secondary">Shipping</span>
                        <span class="text-white fw-bold">FREE</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-white fs-5 fw-bold">Paid Total</span>
                        <span class="text-success fs-4 fw-bold">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping and Customer Details -->
        <div class="glass-panel p-4 mb-4">
            <h4 class="brand-font mb-4 text-white">Delivery & Contact</h4>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <span class="text-secondary small d-block">Recipient Name</span>
                    <strong class="text-white">{{ $order->user->name }}</strong>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-secondary small d-block">Recipient Phone</span>
                    <span class="text-white">{{ $order->phone }}</span>
                </div>
                <div class="col-12">
                    <span class="text-secondary small d-block">Shipping Address</span>
                    <span class="text-white" style="white-space: pre-line;">{{ $order->shipping_address }}</span>
                </div>
            </div>
        </div>

        <!-- Status History Audit Logs -->
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4 text-white"><i class="fa-solid fa-history me-2 text-primary"></i>Status Activity Logs</h4>
            @if($order->histories->isEmpty())
                <p class="text-secondary small mb-0">No activity logs recorded yet.</p>
            @else
                <div class="timeline-logs">
                    @foreach($order->histories as $history)
                        <div class="d-flex mb-3 align-items-start border-start border-secondary border-opacity-25 ps-3 position-relative" style="margin-left: 10px;">
                            <div class="position-absolute bg-primary rounded-circle" style="width: 10px; height: 10px; left: -5px; top: 6px;"></div>
                            <div class="ms-2">
                                <strong class="text-white d-block small">
                                    {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                                    @if($history->user)
                                        <span class="text-secondary fw-normal">by {{ $history->user->name }}</span>
                                    @endif
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

    <!-- Edit Status & Tracking Form Sidebar -->
    <div class="col-lg-4">
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4">Update Order Control</h4>

            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="status" class="form-label form-label-custom">Delivery Status</label>
                    <select name="status" id="status" class="form-select form-control-custom" required>
                        @foreach(\App\Models\Order::STATUSES as $statusOpt)
                            <option value="{{ $statusOpt }}" {{ $order->status === $statusOpt ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $statusOpt)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="payment_status" class="form-label form-label-custom">Payment Status</label>
                    <select name="payment_status" id="payment_status" class="form-select form-control-custom" required>
                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ $order->payment_status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="shipping_provider" class="form-label form-label-custom">Shipping Provider</label>
                    <input type="text" name="shipping_provider" id="shipping_provider" class="form-control form-control-custom" value="{{ $order->shipping_provider }}" placeholder="e.g. FedEx, Delhivery, DHL">
                </div>

                <div class="mb-3">
                    <label for="tracking_number" class="form-label form-label-custom">Tracking Number</label>
                    <input type="text" name="tracking_number" id="tracking_number" class="form-control form-control-custom" value="{{ $order->tracking_number }}" placeholder="e.g. 1234567890">
                </div>

                <div class="mb-3">
                    <label for="tracking_url" class="form-label form-label-custom">Tracking URL</label>
                    <input type="url" name="tracking_url" id="tracking_url" class="form-control form-control-custom" value="{{ $order->tracking_url }}" placeholder="https://www.fedex.com/...">
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label form-label-custom">Update Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control form-control-custom" placeholder="Provide details about status change..."></textarea>
                </div>

                <button type="submit" class="btn btn-premium w-100 py-3">Save Status Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="row g-4">
    <!-- Navigation Sidebar -->
    <div class="col-md-3">
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4">Account</h4>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('profile.index') }}" class="text-decoration-none py-2 text-secondary">
                    <i class="fa-regular fa-user me-2"></i> Profile Details
                </a>
                <a href="{{ route('orders.index') }}" class="text-decoration-none py-2 text-primary fw-bold">
                    <i class="fa-solid fa-truck-ramp-box me-2"></i> Order History
                </a>
            </div>
        </div>
    </div>

    <!-- Order History list -->
    <div class="col-md-9">
        <div class="glass-panel p-4 p-md-5">
            <h3 class="brand-font mb-4">Order History</h3>

            @if($orders->isEmpty())
                <div class="text-center py-5 text-secondary">
                    <i class="fa-solid fa-box-open fs-1 mb-3 opacity-50"></i>
                    <p>You haven't placed any orders yet.</p>
                    <a href="{{ route('shop.list') }}" class="btn btn-premium mt-2">Browse Shirts</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td class="fw-bold text-white">{{ $order->order_number }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($order->status === 'pending')
                                            <span class="badge bg-warning text-dark badge-custom">{{ $order->status }}</span>
                                        @elseif($order->status === 'processing')
                                            <span class="badge bg-info text-dark badge-custom">{{ $order->status }}</span>
                                        @elseif($order->status === 'shipped')
                                            <span class="badge bg-primary badge-custom">{{ $order->status }}</span>
                                        @elseif($order->status === 'delivered')
                                            <span class="badge bg-success badge-custom">{{ $order->status }}</span>
                                        @else
                                            <span class="badge bg-danger badge-custom">{{ $order->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-premium-outline btn-sm py-1">
                                            Track & View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

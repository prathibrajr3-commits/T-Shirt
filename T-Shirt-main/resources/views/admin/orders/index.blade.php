@extends('layouts.admin')

@section('title', 'Manage Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Orders</h1>
</div>

<div class="glass-panel p-4">
    <h4 class="brand-font mb-4">Customer Orders</h4>

    @if($orders->isEmpty())
        <p class="text-secondary py-5 text-center"><i class="fa-solid fa-box-open fs-2 mb-3 d-block opacity-50"></i> No customer orders found in the database.</p>
    @else
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Delivery Status</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td class="fw-bold text-white">{{ $order->order_number }}</td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td class="text-uppercase">{{ $order->payment_method }}</td>
                            <td>
                                @if($order->payment_status === 'completed')
                                    <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> Paid</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="text-warning"><i class="fa-solid fa-clock me-1"></i> Pending</span>
                                @else
                                    <span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Failed</span>
                                @endif
                            </td>
                            <td>
                                @if($order->status === 'pending')
                                    <span class="badge bg-warning text-dark">{{ $order->status }}</span>
                                @elseif($order->status === 'processing')
                                    <span class="badge bg-info text-dark">{{ $order->status }}</span>
                                @elseif($order->status === 'shipped')
                                    <span class="badge bg-primary">{{ $order->status }}</span>
                                @elseif($order->status === 'delivered')
                                    <span class="badge bg-success">{{ $order->status }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-white">₹{{ number_format($order->total_amount, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-premium btn-sm py-1">
                                    Manage
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
@endsection

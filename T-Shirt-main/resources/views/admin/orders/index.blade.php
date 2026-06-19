@extends('layouts.admin')

@section('title', 'Manage Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Orders</h1>
</div>

<div class="glass-panel p-4">
    <h4 class="brand-font mb-4">Customer Orders</h4>

    <!-- Search & Filter Bar -->
    <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="search" class="form-label form-label-custom text-secondary small">Search Order</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" id="search" class="form-control form-control-custom" value="{{ request('search') }}" placeholder="Order #, Customer, Email, Phone...">
            </div>
        </div>
        
        <div class="col-md-3">
            <label for="status" class="form-label form-label-custom text-secondary small">Status Filter</label>
            <select name="status" id="status" class="form-select form-control-custom">
                <option value="">All Statuses</option>
                @foreach(\App\Models\Order::STATUSES as $statusOpt)
                    <option value="{{ $statusOpt }}" {{ request('status') === $statusOpt ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $statusOpt)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label for="sort" class="form-label form-label-custom text-secondary small">Sort By</label>
            <select name="sort" id="sort" class="form-select form-control-custom">
                <option value="newest" {{ request('sort') !== 'oldest' ? 'selected' : '' }}>Newest Placed</option>
                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest Placed</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-premium w-100 py-2"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-premium-outline py-2"><i class="fa-solid fa-rotate-left"></i></a>
            @endif
        </div>
    </form>

    @if($orders->isEmpty())
        <p class="text-secondary py-5 text-center"><i class="fa-solid fa-box-open fs-2 mb-3 d-block opacity-50"></i> No customer orders found matching the filter.</p>
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
                            <td>{{ $order->created_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</td>
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
                                <span class="badge {{ $order->statusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
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

@extends('layouts.admin')

@section('title', 'Manage Returns & Refunds')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Returns & Refunds</h1>
</div>

<div class="glass-panel p-4">
    <h4 class="brand-font mb-4">Customer Return Requests</h4>

    <!-- Search & Filter Bar -->
    <form action="{{ route('admin.returns.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="search" class="form-label form-label-custom text-secondary small">Search Request</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" id="search" class="form-control form-control-custom" value="{{ request('search') }}" placeholder="Order #, Customer Name or Email...">
            </div>
        </div>
        
        <div class="col-md-3">
            <label for="status" class="form-label form-label-custom text-secondary small">Status Filter</label>
            <select name="status" id="status" class="form-select form-control-custom">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="sort" class="form-label form-label-custom text-secondary small">Sort By</label>
            <select name="sort" id="sort" class="form-select form-control-custom">
                <option value="newest" {{ request('sort') !== 'oldest' ? 'selected' : '' }}>Newest Requested</option>
                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest Requested</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-premium w-100 py-2"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.returns.index') }}" class="btn btn-premium-outline py-2"><i class="fa-solid fa-rotate-left"></i></a>
            @endif
        </div>
    </form>

    @if($returnRequests->isEmpty())
        <p class="text-secondary py-5 text-center"><i class="fa-solid fa-rotate-left fs-2 mb-3 d-block opacity-50 text-warning"></i> No return requests found matching the filter.</p>
    @else
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Requested Date</th>
                        <th>Customer</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end">Order Total</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returnRequests as $req)
                        <tr>
                            <td class="fw-bold text-white">{{ $req->order->order_number }}</td>
                            <td>{{ ($req->requested_at ?? $req->created_at)->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</td>
                            <td>{{ $req->user->name }}</td>
                            <td>{{ $req->reason }}</td>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="badge bg-warning text-dark badge-custom">Pending</span>
                                @elseif($req->status === 'approved')
                                    <span class="badge bg-info text-dark badge-custom">Approved</span>
                                @elseif($req->status === 'rejected')
                                    <span class="badge bg-danger badge-custom">Rejected</span>
                                @elseif($req->status === 'completed')
                                    <span class="badge bg-success badge-custom">Completed</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-white">₹{{ number_format($req->order->total_amount, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.returns.show', $req->id) }}" class="btn btn-premium btn-sm py-1">
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
            {{ $returnRequests->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection

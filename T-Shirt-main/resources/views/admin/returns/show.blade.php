@extends('layouts.admin')

@section('title', 'Manage Return Request: Order ' . $returnRequest->order->order_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Manage Return Request</h1>
    <a href="{{ route('admin.returns.index') }}" class="btn btn-premium-outline"><i class="fa-solid fa-arrow-left me-2"></i> Back to Returns</a>
</div>

<div class="row g-4">
    <!-- Return request details & Order info -->
    <div class="col-lg-8">
        <!-- Return details -->
        <div class="glass-panel p-4 mb-4">
            <h4 class="brand-font mb-3 text-white">Return Request Details</h4>
            <p class="text-secondary small">Submitted by {{ $returnRequest->user->name }} ({{ $returnRequest->user->email }}) on {{ ($returnRequest->requested_at ?? $returnRequest->created_at)->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</p>

            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <span class="text-secondary small d-block">Reason for Return</span>
                    <strong class="text-white fs-5">{{ $returnRequest->reason }}</strong>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="text-secondary small d-block">Current Request Status</span>
                    @if($returnRequest->status === 'pending')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-1 badge-custom">Pending</span>
                    @elseif($returnRequest->status === 'approved')
                        <span class="badge bg-info text-dark fs-6 px-3 py-1 badge-custom">Approved</span>
                    @elseif($returnRequest->status === 'rejected')
                        <span class="badge bg-danger fs-6 px-3 py-1 badge-custom">Rejected</span>
                    @elseif($returnRequest->status === 'completed')
                        <span class="badge bg-success fs-6 px-3 py-1 badge-custom">Completed</span>
                    @endif
                </div>

                <div class="col-12 mb-3">
                    <span class="text-secondary small d-block">Customer Description</span>
                    <p class="text-white bg-dark bg-opacity-25 p-3 rounded border border-secondary border-opacity-10 mt-1" style="white-space: pre-line;">
                        {{ $returnRequest->description ?? 'No description provided.' }}
                    </p>
                </div>

                @if($returnRequest->image)
                    <div class="col-12 mb-3">
                        <span class="text-secondary small d-block mb-2">Customer Proof Photo</span>
                        <a href="{{ asset('storage/' . $returnRequest->image) }}" target="_blank" class="d-inline-block">
                            <img src="{{ asset('storage/' . $returnRequest->image) }}" class="rounded img-thumbnail bg-dark border-secondary border-opacity-25" style="max-width: 300px; max-height: 300px; object-fit: contain;" alt="Customer proof">
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Ordered Items Summary -->
        <div class="glass-panel p-4 mb-4">
            <h4 class="brand-font mb-4 text-white">Order Details: {{ $returnRequest->order->order_number }}</h4>
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
                        @foreach($returnRequest->order->items as $item)
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

            <div class="d-flex justify-content-between align-items-center">
                <span class="text-secondary">Order Original Status</span>
                <span class="badge bg-secondary fs-6 badge-custom text-white border border-secondary border-opacity-25">{{ ucfirst(str_replace('_', ' ', $returnRequest->order->status)) }}</span>
            </div>
        </div>

        <!-- History logs -->
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4 text-white"><i class="fa-solid fa-history me-2 text-primary"></i>Linked Order History Logs</h4>
            <div class="timeline-logs">
                @foreach($returnRequest->order->histories as $history)
                    <div class="d-flex mb-3 align-items-start border-start border-secondary border-opacity-25 ps-3 position-relative" style="margin-left: 10px;">
                        <div class="position-absolute bg-primary rounded-circle" style="width: 10px; height: 10px; left: -5px; top: 6px;"></div>
                        <div class="ms-2">
                            <strong class="text-white d-block small">
                                {{ $history->status }}
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
        </div>
    </div>

    <!-- Admin workflow actions sidebar -->
    <div class="col-lg-4">
        @if($returnRequest->status === 'pending')
            <!-- Approve Request -->
            <div class="glass-panel p-4 mb-4">
                <h4 class="brand-font mb-3 text-white"><i class="fa-solid fa-circle-check me-2 text-success"></i>Approve Return</h4>
                <p class="text-secondary small">Approve the request to authorize return shipping.</p>
                <form action="{{ route('admin.returns.approve', $returnRequest->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label form-label-custom">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" id="approve_notes" rows="3" class="form-control form-control-custom" placeholder="e.g. Return authorized, instructions sent."></textarea>
                    </div>
                    <button type="submit" class="btn btn-premium w-100 py-2">Approve Request</button>
                </form>
            </div>

            <!-- Reject Request -->
            <div class="glass-panel p-4">
                <h4 class="brand-font mb-3 text-white"><i class="fa-solid fa-circle-xmark me-2 text-danger"></i>Reject Return</h4>
                <p class="text-secondary small">Reject the request. Reason is required.</p>
                <form action="{{ route('admin.returns.reject', $returnRequest->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="reject_notes" class="form-label form-label-custom">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="admin_notes" id="reject_notes" rows="3" class="form-control form-control-custom" placeholder="Provide reason for rejection..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 py-2" style="background-color: var(--danger-color); border: none;">Reject Request</button>
                </form>
            </div>
        @elseif($returnRequest->status === 'approved')
            <!-- Complete Refund -->
            <div class="glass-panel p-4">
                <h4 class="brand-font mb-3 text-white"><i class="fa-solid fa-hand-holding-dollar me-2 text-success"></i>Complete Refund</h4>
                <p class="text-secondary small">Confirm return package received and process refund.</p>
                <form action="{{ route('admin.returns.complete', $returnRequest->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="refund_amount" class="form-label form-label-custom">Refund Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white">₹</span>
                            <input type="number" name="refund_amount" id="refund_amount" step="0.01" min="0" max="{{ $returnRequest->order->total_amount }}" class="form-control form-control-custom" value="{{ $returnRequest->order->total_amount }}" required>
                        </div>
                        <div class="form-text text-secondary" style="font-size: 0.75rem;">Max available: ₹{{ number_format($returnRequest->order->total_amount, 2) }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="refund_reference" class="form-label form-label-custom">Refund Reference / Txn ID <span class="text-danger">*</span></label>
                        <input type="text" name="refund_reference" id="refund_reference" class="form-control form-control-custom" placeholder="e.g. refund_123456, TXN-998877" required>
                    </div>

                    <div class="mb-4">
                        <label for="complete_notes" class="form-label form-label-custom">Notes (Optional)</label>
                        <textarea name="admin_notes" id="complete_notes" rows="3" class="form-control form-control-custom" placeholder="e.g. Refund completed via Razorpay."></textarea>
                    </div>

                    <button type="submit" class="btn btn-premium w-100 py-3">Complete Refund</button>
                </form>
            </div>
        @else
            <!-- Request Resolved Summary -->
            <div class="glass-panel p-4">
                <h4 class="brand-font mb-3 text-white">Resolution Summary</h4>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Resolved Date</span>
                    <strong class="text-white">
                        @if($returnRequest->status === 'completed')
                            {{ $returnRequest->completed_at ? $returnRequest->completed_at->timezone(config('app.timezone'))->format('M d, Y h:i A') : 'N/A' }}
                        @else
                            {{ $returnRequest->rejected_at ? $returnRequest->rejected_at->timezone(config('app.timezone'))->format('M d, Y h:i A') : 'N/A' }}
                        @endif
                    </strong>
                </div>

                @if($returnRequest->status === 'completed')
                    <div class="mb-3">
                        <span class="text-secondary small d-block">Refunded Amount</span>
                        <strong class="text-success fs-5">₹{{ number_format($returnRequest->refund_amount, 2) }}</strong>
                    </div>
                    <div class="mb-3">
                        <span class="text-secondary small d-block">Refund Reference</span>
                        <strong class="text-white">{{ $returnRequest->refund_reference }}</strong>
                    </div>
                @endif

                <div class="mb-0">
                    <span class="text-secondary small d-block">Admin Notes</span>
                    <p class="text-light small mt-1 mb-0 p-2 rounded bg-dark bg-opacity-25 border border-secondary border-opacity-10">
                        {{ $returnRequest->admin_notes ?? 'None' }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

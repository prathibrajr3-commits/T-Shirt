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
                <div class="mt-3 mt-md-0 d-flex align-items-center gap-2 flex-wrap">
                    @if($order->status === 'cancelled')
                        <span class="badge bg-danger fs-6 px-3 py-2 badge-custom">Cancelled</span>
                    @elseif($order->status === 'refunded')
                        <span class="badge bg-dark fs-6 px-3 py-2 badge-custom text-secondary border border-secondary border-opacity-25">Refunded</span>
                    @else
                        <span class="badge bg-success fs-6 px-3 py-2 badge-custom">Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        @if(in_array($order->status, ['pending', 'confirmed']))
                            @can('cancel', $order)
                                <button type="button" class="btn btn-danger btn-sm px-3 py-2 badge-custom text-white border-0" data-bs-toggle="modal" data-bs-target="#cancelOrderModal" style="background-color: var(--danger-color);">
                                    <i class="fa-solid fa-ban me-1"></i> Cancel Order
                                </button>
                            @endcan
                        @endif
                        @if($order->status === 'delivered')
                            @if($order->returnRequest)
                                @if($order->returnRequest->status === 'pending')
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2 badge-custom"><i class="fa-solid fa-rotate-left me-1"></i> Return Pending</span>
                                @elseif($order->returnRequest->status === 'approved')
                                    <span class="badge bg-info text-dark fs-6 px-3 py-2 badge-custom"><i class="fa-solid fa-circle-check me-1"></i> Return Approved</span>
                                @elseif($order->returnRequest->status === 'rejected')
                                    <span class="badge bg-danger fs-6 px-3 py-2 badge-custom"><i class="fa-solid fa-circle-xmark me-1"></i> Return Rejected</span>
                                @elseif($order->returnRequest->status === 'completed')
                                    <span class="badge bg-success fs-6 px-3 py-2 badge-custom"><i class="fa-solid fa-hand-holding-dollar me-1"></i> Refund Completed</span>
                                @endif
                            @else
                                @if($order->delivered_at && $order->delivered_at->addDays(7)->isPast())
                                    <span class="badge bg-secondary fs-6 px-3 py-2 badge-custom">Return window expired</span>
                                @else
                                    <button type="button" class="btn btn-warning btn-sm px-3 py-2 badge-custom text-dark border-0" data-bs-toggle="modal" data-bs-target="#returnOrderModal" style="background-color: var(--warning-color);">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Request Return
                                    </button>
                                @endif
                            @endif
                        @endif
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
                <div class="p-4 rounded border border-danger border-opacity-25 bg-danger bg-opacity-10">
                    <h5 class="brand-font text-danger mb-3"><i class="fa-solid fa-circle-xmark me-2"></i>Order Cancelled</h5>
                    <div class="mb-2">
                        <span class="text-secondary small d-block">Cancelled On:</span>
                        <strong class="text-white">{{ $order->customer_cancelled_at ? $order->customer_cancelled_at->timezone(config('app.timezone'))->format('F d, Y h:i A') : ($order->cancelled_at ? $order->cancelled_at->timezone(config('app.timezone'))->format('F d, Y h:i A') : 'N/A') }}</strong>
                    </div>
                    <div class="mb-0">
                        <span class="text-secondary small d-block">Reason:</span>
                        <strong class="text-white">{{ $order->customer_cancel_reason ?? 'No reason provided' }}</strong>
                    </div>
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

        <!-- Return Request History -->
        @if($order->returnRequest)
        <div class="glass-panel p-4 mb-4">
            <h4 class="brand-font mb-4 text-white"><i class="fa-solid fa-rotate-left me-2 text-warning"></i>Return Request Timeline</h4>
            <div class="timeline-logs">
                <!-- Requested -->
                <div class="d-flex mb-3 align-items-start border-start border-secondary border-opacity-25 ps-3 position-relative" style="margin-left: 10px;">
                    <div class="position-absolute bg-primary rounded-circle" style="width: 10px; height: 10px; left: -5px; top: 6px;"></div>
                    <div class="ms-2">
                        <strong class="text-white d-block small">
                            Requested
                        </strong>
                        <span class="text-secondary small d-block mb-1" style="font-size: 0.8rem;">
                            {{ $order->returnRequest->requested_at ? $order->returnRequest->requested_at->timezone(config('app.timezone'))->format('M d, Y h:i A') : $order->returnRequest->created_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                        </span>
                        <p class="text-light small mb-1">Reason: <strong>{{ $order->returnRequest->reason }}</strong></p>
                        @if($order->returnRequest->description)
                            <p class="text-secondary small mb-2">Description: {{ $order->returnRequest->description }}</p>
                        @endif
                        @if($order->returnRequest->image)
                            <div class="mb-0 mt-2">
                                <span class="text-secondary small d-block mb-1">Attached Photo:</span>
                                <a href="{{ asset('storage/' . $order->returnRequest->image) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $order->returnRequest->image) }}" class="rounded border border-secondary border-opacity-25" style="max-width: 150px; max-height: 150px; object-fit: contain;" alt="Return image">
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Approved / Rejected -->
                @if($order->returnRequest->status === 'approved' || $order->returnRequest->status === 'completed')
                    <div class="d-flex mb-3 align-items-start border-start border-secondary border-opacity-25 ps-3 position-relative" style="margin-left: 10px;">
                        <div class="position-absolute bg-success rounded-circle" style="width: 10px; height: 10px; left: -5px; top: 6px;"></div>
                        <div class="ms-2">
                            <strong class="text-success d-block small">
                                Approved
                            </strong>
                            <span class="text-secondary small d-block mb-1" style="font-size: 0.8rem;">
                                {{ $order->returnRequest->approved_at ? $order->returnRequest->approved_at->timezone(config('app.timezone'))->format('M d, Y h:i A') : '' }}
                            </span>
                            @if($order->returnRequest->admin_notes)
                                <p class="text-light small mb-0">Admin Notes: {{ $order->returnRequest->admin_notes }}</p>
                            @endif
                        </div>
                    </div>
                @elseif($order->returnRequest->status === 'rejected')
                    <div class="d-flex mb-3 align-items-start border-start border-secondary border-opacity-25 ps-3 position-relative" style="margin-left: 10px;">
                        <div class="position-absolute bg-danger rounded-circle" style="width: 10px; height: 10px; left: -5px; top: 6px;"></div>
                        <div class="ms-2">
                            <strong class="text-danger d-block small">
                                Rejected
                            </strong>
                            <span class="text-secondary small d-block mb-1" style="font-size: 0.8rem;">
                                {{ $order->returnRequest->rejected_at ? $order->returnRequest->rejected_at->timezone(config('app.timezone'))->format('M d, Y h:i A') : '' }}
                            </span>
                            @if($order->returnRequest->admin_notes)
                                <p class="text-light small mb-0">Reason: {{ $order->returnRequest->admin_notes }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Refunded -->
                @if($order->returnRequest->status === 'completed')
                    <div class="d-flex mb-0 align-items-start ps-3 position-relative" style="margin-left: 10px;">
                        <div class="position-absolute bg-success rounded-circle" style="width: 10px; height: 10px; left: -5px; top: 6px;"></div>
                        <div class="ms-2">
                            <strong class="text-success d-block small">
                                Refunded
                            </strong>
                            <span class="text-secondary small d-block mb-1" style="font-size: 0.8rem;">
                                {{ $order->returnRequest->completed_at ? $order->returnRequest->completed_at->timezone(config('app.timezone'))->format('M d, Y h:i A') : '' }}
                            </span>
                            @if($order->returnRequest->refund_amount)
                                <p class="text-light small mb-1">Refunded Amount: <strong>₹{{ number_format($order->returnRequest->refund_amount, 2) }}</strong></p>
                            @endif
                            @if($order->returnRequest->refund_reference)
                                <p class="text-secondary small mb-1">Reference: {{ $order->returnRequest->refund_reference }}</p>
                            @endif
                            @if($order->returnRequest->admin_notes)
                                <p class="text-light small mb-0">Notes: {{ $order->returnRequest->admin_notes }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

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

<!-- Cancel Order Modal -->
@if(in_array($order->status, ['pending', 'confirmed']))
    @can('cancel', $order)
        <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border-secondary" style="background-color: var(--bg-dark) !important; border: 1px solid var(--border-color) !important;">
                    <div class="modal-header border-secondary border-opacity-50">
                        <h5 class="modal-title brand-font text-white" id="cancelOrderModalLabel">Cancel Order</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                        @csrf
                        <div class="modal-body text-light">
                            <p>Are you sure you want to cancel this order?<br><span class="text-danger small">This action cannot be undone.</span></p>
                            
                            <div class="mb-3">
                                <label for="cancel_reason" class="form-label form-label-custom">Cancellation Reason <span class="text-danger">*</span></label>
                                <select name="cancel_reason" id="cancel_reason" class="form-select form-control-custom" required>
                                    <option value="" disabled selected>Select a reason...</option>
                                    <option value="Ordered by mistake">Ordered by mistake</option>
                                    <option value="Found a better price">Found a better price</option>
                                    <option value="Delivery takes too long">Delivery takes too long</option>
                                    <option value="Want to change product">Want to change product</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3" id="other_reason_container" style="display: none;">
                                <label for="reason_details" class="form-label form-label-custom">Reason Details</label>
                                <textarea name="reason_details" id="reason_details" rows="3" class="form-control form-control-custom" placeholder="Please provide more details..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary border-opacity-50">
                            <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">No, Keep Order</button>
                            <button type="submit" class="btn btn-danger" style="background-color: var(--danger-color); border: none;">Yes, Cancel Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endif

<!-- Return Order Modal -->
@if($order->status === 'delivered' && $order->canRequestReturn())
    <div class="modal fade" id="returnOrderModal" tabindex="-1" aria-labelledby="returnOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary" style="background-color: var(--bg-dark) !important; border: 1px solid var(--border-color) !important;">
                <div class="modal-header border-secondary border-opacity-50">
                    <h5 class="modal-title brand-font text-white" id="returnOrderModalLabel">Request Return & Refund</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('orders.return', $order->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body text-light">
                        <p>Please fill out the form below to request a return and refund for your order.</p>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label form-label-custom">Reason for Return <span class="text-danger">*</span></label>
                            <select name="reason" id="reason" class="form-select form-control-custom" required>
                                <option value="" disabled selected>Select a reason...</option>
                                <option value="Wrong Size">Wrong Size</option>
                                <option value="Wrong Product">Wrong Product</option>
                                <option value="Damaged Product">Damaged Product (Photo required)</option>
                                <option value="Quality Issue">Quality Issue</option>
                                <option value="Changed Mind">Changed Mind</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label form-label-custom">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3" class="form-control form-control-custom" placeholder="Please provide details..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label form-label-custom">Upload Photo <span id="photo_required_label" style="display: none;" class="text-danger">*</span></label>
                            <input type="file" name="image" id="image" class="form-control form-control-custom" accept="image/png, image/jpeg, image/jpg">
                            <div class="form-text text-secondary" style="font-size: 0.75rem;">Supported formats: PNG, JPG, JPEG. Max size: 5 MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary border-opacity-50">
                        <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-dark font-weight-bold" style="background-color: var(--warning-color); border: none;">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cancelReasonSelect = document.getElementById('cancel_reason');
        const otherReasonContainer = document.getElementById('other_reason_container');
        const reasonDetailsTextarea = document.getElementById('reason_details');

        if (cancelReasonSelect) {
            cancelReasonSelect.addEventListener('change', function () {
                if (this.value === 'Other') {
                    otherReasonContainer.style.display = 'block';
                    reasonDetailsTextarea.setAttribute('required', 'required');
                } else {
                    otherReasonContainer.style.display = 'none';
                    reasonDetailsTextarea.removeAttribute('required');
                }
            });
        }

        // Return request photo requirement toggle based on reason
        const returnReasonSelect = document.getElementById('reason');
        const imageInput = document.getElementById('image');
        const photoRequiredLabel = document.getElementById('photo_required_label');

        if (returnReasonSelect && imageInput) {
            returnReasonSelect.addEventListener('change', function () {
                if (this.value === 'Damaged Product') {
                    imageInput.setAttribute('required', 'required');
                    if (photoRequiredLabel) {
                        photoRequiredLabel.style.display = 'inline';
                    }
                } else {
                    imageInput.removeAttribute('required');
                    if (photoRequiredLabel) {
                        photoRequiredLabel.style.display = 'none';
                    }
                }
            });
        }
    });
</script>
@endsection
@endsection

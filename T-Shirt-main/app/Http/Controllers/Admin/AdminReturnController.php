<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Notifications\RefundCompletedNotification;
use App\Notifications\ReturnRequestApprovedNotification;
use App\Notifications\ReturnRequestRejectedNotification;
use Illuminate\Http\Request;

class AdminReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order', 'user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter/Search by order number or customer name/email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($qo) use ($search) {
                    $qo->where('order_number', 'like', "%{$search}%");
                })->orWhereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $returnRequests = $query->paginate(15)->withQueryString();

        return view('admin.returns.index', compact('returnRequests'));
    }

    public function show($id)
    {
        $returnRequest = ReturnRequest::with(['order.items.product', 'user', 'order.histories.user'])->findOrFail($id);
        return view('admin.returns.show', compact('returnRequest'));
    }

    public function approve(Request $request, $id)
    {
        $returnRequest = ReturnRequest::findOrFail($id);

        // Security check: cannot approve if already rejected
        if ($returnRequest->status === ReturnRequest::STATUS_REJECTED) {
            return back()->with('error', 'Cannot approve a rejected return request.');
        }

        // Cannot approve if already completed
        if ($returnRequest->status === ReturnRequest::STATUS_COMPLETED) {
            return back()->with('error', 'Return request is already completed.');
        }

        // Cannot approve if already approved
        if ($returnRequest->status === ReturnRequest::STATUS_APPROVED) {
            return back()->with('error', 'Return request is already approved.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $returnRequest->update([
            'status' => ReturnRequest::STATUS_APPROVED,
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Add return audit history
        $returnRequest->order->histories()->create([
            'status' => 'Return Approved',
            'notes' => "Return request approved.\nAdmin Notes: " . ($request->admin_notes ?? 'None'),
            'updated_by' => auth()->id(),
        ]);

        // Dispatch Notification
        $returnRequest->user->notify(new ReturnRequestApprovedNotification($returnRequest));

        return back()->with('success', 'Return request approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $returnRequest = ReturnRequest::findOrFail($id);

        if ($returnRequest->status !== ReturnRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending return requests can be rejected.');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $returnRequest->update([
            'status' => ReturnRequest::STATUS_REJECTED,
            'rejected_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Add return audit history
        $returnRequest->order->histories()->create([
            'status' => 'Return Rejected',
            'notes' => "Return request rejected.\nReason: " . $request->admin_notes,
            'updated_by' => auth()->id(),
        ]);

        // Dispatch Notification
        $returnRequest->user->notify(new ReturnRequestRejectedNotification($returnRequest));

        return back()->with('success', 'Return request rejected successfully.');
    }

    public function complete(Request $request, $id)
    {
        $returnRequest = ReturnRequest::findOrFail($id);

        // Security check: cannot complete pending request
        if ($returnRequest->status === ReturnRequest::STATUS_PENDING) {
            return back()->with('error', 'Cannot complete a pending request. Approve it first.');
        }

        // Cannot complete if already rejected
        if ($returnRequest->status === ReturnRequest::STATUS_REJECTED) {
            return back()->with('error', 'Cannot complete a rejected request.');
        }

        // Cannot complete if already completed
        if ($returnRequest->status === ReturnRequest::STATUS_COMPLETED) {
            return back()->with('error', 'Return request is already completed.');
        }

        $request->validate([
            'refund_amount' => 'required|numeric|min:0|max:' . $returnRequest->order->total_amount,
            'refund_reference' => 'required|string|max:255',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $returnRequest->update([
            'status' => ReturnRequest::STATUS_COMPLETED,
            'completed_at' => now(),
            'refund_amount' => $request->refund_amount,
            'refund_reference' => $request->refund_reference,
            'admin_notes' => $request->admin_notes,
        ]);

        // Update linked Order status to refunded
        $returnRequest->order->updateStatus(
            Order::STATUS_REFUNDED,
            "Refund Completed. Reference: " . $request->refund_reference . " | Amount: ₹" . number_format($request->refund_amount, 2),
            null,
            null,
            null,
            auth()->id()
        );

        // Add return audit history
        $returnRequest->order->histories()->create([
            'status' => 'Refund Completed',
            'notes' => "Refund of ₹" . number_format($request->refund_amount, 2) . " completed.\nReference: " . $request->refund_reference . ($request->admin_notes ? "\nAdmin Notes: " . $request->admin_notes : ""),
            'updated_by' => auth()->id(),
        ]);

        // Dispatch Notification
        $returnRequest->user->notify(new RefundCompletedNotification($returnRequest));

        return back()->with('success', 'Refund completed and order refunded successfully.');
    }
}

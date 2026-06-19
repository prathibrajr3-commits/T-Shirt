<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Notifications\AdminReturnRequestSubmittedNotification;
use App\Notifications\RefundCompletedNotification;
use App\Notifications\ReturnRequestApprovedNotification;
use App\Notifications\ReturnRequestRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReturnRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer1;
    private User $customer2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Store Admin',
            'email' => 'admin@tshirt.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->customer1 = User::create([
            'name' => 'Customer One',
            'email' => 'cust1@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'phone' => '1234567890',
            'address' => 'Addr 1',
        ]);

        $this->customer2 = User::create([
            'name' => 'Customer Two',
            'email' => 'cust2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'phone' => '0987654321',
            'address' => 'Addr 2',
        ]);
    }

    /**
     * Test customer can request return.
     */
    public function test_customer_can_request_return(): void
    {
        Notification::fake();
        Storage::fake('public');

        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV101',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(2),
        ]);

        $image = UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($this->customer1)->post(route('orders.return', $order->id), [
            'reason' => 'Wrong Size',
            'description' => 'Too tight at the shoulders.',
            'image' => $image,
        ]);

        $response->assertRedirect(route('orders.show', $order->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('return_requests', [
            'order_id' => $order->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Wrong Size',
            'description' => 'Too tight at the shoulders.',
            'status' => 'pending',
        ]);

        $returnRequest = ReturnRequest::where('order_id', $order->id)->first();
        $this->assertNotNull($returnRequest->image);
        Storage::disk('public')->assertExists($returnRequest->image);

        // Check timeline audit history
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'Return Requested',
            'updated_by' => $this->customer1->id,
        ]);

        Notification::assertSentTo($this->admin, AdminReturnRequestSubmittedNotification::class);
    }

    /**
     * Test customer cannot return another user's order.
     */
    public function test_customer_cannot_return_another_users_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV102',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->customer2)->post(route('orders.return', $order->id), [
            'reason' => 'Wrong Size',
            'description' => 'Not mine anyway.',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('return_requests', [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Test return window is enforced (returns only within 7 days).
     */
    public function test_return_window_is_enforced(): void
    {
        // 8 days ago - expired
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV103',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(8),
        ]);

        $response = $this->actingAs($this->customer1)->post(route('orders.return', $order->id), [
            'reason' => 'Wrong Size',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Return window expired.');
        $this->assertDatabaseMissing('return_requests', [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Test duplicate return requests are blocked.
     */
    public function test_duplicate_return_requests_are_blocked(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV104',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(1),
        ]);

        ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Wrong Size',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // Attempt duplicate request
        $response = $this->actingAs($this->customer1)->post(route('orders.return', $order->id), [
            'reason' => 'Changed Mind',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'A return request already exists for this order.');
        $this->assertEquals(1, ReturnRequest::where('order_id', $order->id)->count());
    }

    /**
     * Test damaged product requires photo upload.
     */
    public function test_damaged_product_requires_photo_upload(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV105',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(1),
        ]);

        // Post without photo
        $response = $this->actingAs($this->customer1)->post(route('orders.return', $order->id), [
            'reason' => 'Damaged Product',
            'description' => 'Ripped sleeve.',
        ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseMissing('return_requests', [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Test admin approval and rejection workflows.
     */
    public function test_admin_approval_and_rejection_workflows(): void
    {
        Notification::fake();

        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV106',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(1),
        ]);

        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Wrong Size',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // 1. Approve return request
        $response = $this->actingAs($this->admin)->post(route('admin.returns.approve', $returnRequest->id), [
            'admin_notes' => 'Return authorized.',
        ]);

        $response->assertRedirect();
        $returnRequest->refresh();
        $this->assertEquals('approved', $returnRequest->status);
        $this->assertEquals('Return authorized.', $returnRequest->admin_notes);
        $this->assertNotNull($returnRequest->approved_at);

        Notification::assertSentTo($this->customer1, ReturnRequestApprovedNotification::class);

        // Check timeline audit log
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'Return Approved',
            'updated_by' => $this->admin->id,
        ]);

        // 2. Try to reject approved (should fail, as rejection is only for pending)
        $response2 = $this->actingAs($this->admin)->post(route('admin.returns.reject', $returnRequest->id), [
            'admin_notes' => 'Too late.',
        ]);
        $response2->assertSessionHas('error');
        $returnRequest->refresh();
        $this->assertEquals('approved', $returnRequest->status); // unchanged
    }

    /**
     * Test admin cannot approve already rejected request.
     */
    public function test_admin_cannot_approve_already_rejected_request(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV107',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(1),
        ]);

        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Wrong Size',
            'status' => 'rejected',
            'requested_at' => now(),
            'rejected_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.returns.approve', $returnRequest->id), [
            'admin_notes' => 'I changed my mind.',
        ]);

        $response->assertSessionHas('error', 'Cannot approve a rejected return request.');
        $returnRequest->refresh();
        $this->assertEquals('rejected', $returnRequest->status);
    }

    /**
     * Test admin cannot complete pending request.
     */
    public function test_admin_cannot_complete_pending_request(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV108',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(1),
        ]);

        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Wrong Size',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.returns.complete', $returnRequest->id), [
            'refund_amount' => 1000.00,
            'refund_reference' => 'REF-123',
        ]);

        $response->assertSessionHas('error', 'Cannot complete a pending request. Approve it first.');
        $returnRequest->refresh();
        $this->assertEquals('pending', $returnRequest->status);
    }

    /**
     * Test refund completion updates order status to refunded.
     */
    public function test_refund_completion_updates_order_status(): void
    {
        Notification::fake();

        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV109',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now()->subDays(1),
        ]);

        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Wrong Size',
            'status' => 'approved',
            'requested_at' => now(),
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.returns.complete', $returnRequest->id), [
            'refund_amount' => 950.00,
            'refund_reference' => 'TXN-REFUND-999',
            'admin_notes' => 'Refund completed minus shipping.',
        ]);

        $response->assertRedirect();
        
        $returnRequest->refresh();
        $this->assertEquals('completed', $returnRequest->status);
        $this->assertEquals(950.00, $returnRequest->refund_amount);
        $this->assertEquals('TXN-REFUND-999', $returnRequest->refund_reference);

        $order->refresh();
        $this->assertEquals('refunded', $order->status);

        // Check timeline audit logs
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'Refund Completed',
            'updated_by' => $this->admin->id,
        ]);

        Notification::assertSentTo($this->customer1, RefundCompletedNotification::class);
    }

    /**
     * Test return rate calculation in dashboard.
     */
    public function test_return_rate_dashboard_calculation(): void
    {
        // 1. Create 3 delivered orders
        $order1 = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-O1',
            'status' => 'delivered',
            'total_amount' => 100.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now(),
        ]);
        $order2 = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-O2',
            'status' => 'delivered',
            'total_amount' => 200.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'delivered_at' => now(),
        ]);
        $order3 = Order::create([
            'user_id' => $this->customer2->id,
            'order_number' => 'TS-O3',
            'status' => 'delivered',
            'total_amount' => 300.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 2',
            'phone' => '0987654321',
            'delivered_at' => now(),
        ]);

        // 2. Create 1 return request
        ReturnRequest::create([
            'order_id' => $order1->id,
            'user_id' => $this->customer1->id,
            'reason' => 'Wrong Size',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Assert returnRate is 33.333333333333% (or close to it)
        $response->assertViewHas('returnRate', function ($rate) {
            return abs($rate - 33.33) < 0.1;
        });
    }
}

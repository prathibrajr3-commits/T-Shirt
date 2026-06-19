<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer1;
    private User $customer2;
    private Order $order;

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

        $this->order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-TEST12345',
            'status' => 'pending',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);
    }

    /**
     * Test initial order creation logs Placed status history.
     */
    public function test_initial_order_creation_logs_history(): void
    {
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $this->order->id,
            'status' => 'pending',
            'notes' => 'Order placed successfully.',
        ]);
    }

    /**
     * Test authorization: Customer can only view their own order details, admin can view all.
     */
    public function test_order_view_authorization(): void
    {
        // Owner can view
        $response = $this->actingAs($this->customer1)->get(route('orders.show', $this->order->id));
        $response->assertStatus(200);

        // Non-owner cannot view (returns 403)
        $response = $this->actingAs($this->customer2)->get(route('orders.show', $this->order->id));
        $response->assertStatus(403);

        // Admin can view
        $response = $this->actingAs($this->admin)->get(route('orders.show', $this->order->id));
        $response->assertStatus(200);
    }

    /**
     * Test valid order status transitions.
     */
    public function test_valid_status_transitions(): void
    {
        $this->assertTrue($this->order->isValidTransition('confirmed'));
        $this->assertTrue($this->order->isValidTransition('cancelled'));
        $this->assertFalse($this->order->isValidTransition('processing')); // Must go pending -> confirmed first
    }

    /**
     * Test admin can update order status and log audit history.
     */
    public function test_admin_can_update_status_and_records_audit(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)->put(route('admin.orders.update', $this->order->id), [
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'notes' => 'Order was confirmed by telephone call.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->order->refresh();
        $this->assertEquals('confirmed', $this->order->status);
        $this->assertNotNull($this->order->status_changed_at);

        // Verify history logs updated_by
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $this->order->id,
            'status' => 'confirmed',
            'notes' => 'Order was confirmed by telephone call.',
            'updated_by' => $this->admin->id,
        ]);

        Notification::assertSentTo($this->customer1, OrderStatusUpdatedNotification::class);
    }

    /**
     * Test admin cannot update invalid transitions.
     */
    public function test_admin_blocked_on_invalid_status_transition(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.orders.update', $this->order->id), [
            'status' => 'delivered', // pending -> delivered is invalid
            'payment_status' => 'pending',
        ]);

        $response->assertSessionHas('error', 'Invalid order status transition.');
        $this->order->refresh();
        $this->assertEquals('pending', $this->order->status); // unchanged
    }

    /**
     * Test dashboard reporting calculations.
     */
    public function test_dashboard_statistics(): void
    {
        // Change order status to delivered (which completes payment)
        $this->order->updateStatus('confirmed');
        $this->order->updateStatus('processing');
        $this->order->updateStatus('packed');
        $this->order->updateStatus('shipped');
        $this->order->updateStatus('out_for_delivery');
        $this->order->updateStatus('delivered');

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard', ['range' => 'today']));
        $response->assertStatus(200);

        $response->assertViewHas('totalSales', 1000.00);
        $response->assertViewHas('ordersCount', 1);
        $response->assertViewHas('deliveredOrders', 1);
        $response->assertViewHas('reportRevenue', 1000.00);
        $response->assertViewHas('reportAOV', 1000.00);
    }
}

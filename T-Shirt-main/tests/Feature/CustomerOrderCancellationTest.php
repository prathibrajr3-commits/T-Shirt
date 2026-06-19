<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\CustomerOrderCancelledNotification;
use App\Notifications\AdminOrderCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerOrderCancellationTest extends TestCase
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
     * Test customer can cancel pending order.
     */
    public function test_customer_can_cancel_pending_order(): void
    {
        Notification::fake();

        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-PEND123',
            'status' => 'pending',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($this->customer1)->post(route('orders.cancel', $order->id), [
            'cancel_reason' => 'Ordered by mistake',
        ]);

        $response->assertRedirect(route('orders.show', $order->id));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('customer', $order->cancelled_by);
        $this->assertEquals('Ordered by mistake', $order->customer_cancel_reason);
        $this->assertNotNull($order->customer_cancelled_at);
        $this->assertNotNull($order->cancelled_at);
        $this->assertNotNull($order->status_changed_at);

        // Check history log
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'cancelled',
            'notes' => "Order cancelled by customer.\nReason:\nOrdered by mistake",
            'updated_by' => $this->customer1->id,
        ]);

        Notification::assertSentTo($this->customer1, CustomerOrderCancelledNotification::class);
        Notification::assertSentTo($this->admin, AdminOrderCancelledNotification::class);
    }

    /**
     * Test customer can cancel confirmed order.
     */
    public function test_customer_can_cancel_confirmed_order(): void
    {
        Notification::fake();

        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-CONF123',
            'status' => 'confirmed',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($this->customer1)->post(route('orders.cancel', $order->id), [
            'cancel_reason' => 'Other',
            'reason_details' => 'Found a cheaper store.',
        ]);

        $response->assertRedirect(route('orders.show', $order->id));

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('Found a cheaper store.', $order->customer_cancel_reason);

        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'cancelled',
            'notes' => "Order cancelled by customer.\nReason:\nFound a cheaper store.",
            'updated_by' => $this->customer1->id,
        ]);

        Notification::assertSentTo($this->customer1, CustomerOrderCancelledNotification::class);
    }

    /**
     * Test customer cannot cancel processing order.
     */
    public function test_customer_cannot_cancel_processing_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-PROC123',
            'status' => 'processing',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($this->customer1)->post(route('orders.cancel', $order->id), [
            'cancel_reason' => 'Ordered by mistake',
        ]);

        $response->assertStatus(403);
        $order->refresh();
        $this->assertNotEquals('cancelled', $order->status);
    }

    /**
     * Test customer cannot cancel shipped order.
     */
    public function test_customer_cannot_cancel_shipped_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-SHIP123',
            'status' => 'shipped',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($this->customer1)->post(route('orders.cancel', $order->id), [
            'cancel_reason' => 'Ordered by mistake',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test customer cannot cancel delivered order.
     */
    public function test_customer_cannot_cancel_delivered_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DELV123',
            'status' => 'delivered',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($this->customer1)->post(route('orders.cancel', $order->id), [
            'cancel_reason' => 'Ordered by mistake',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test unauthorized user cannot cancel another user's order.
     */
    public function test_unauthorized_user_cannot_cancel_another_users_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-UNAUTH123',
            'status' => 'pending',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($this->customer2)->post(route('orders.cancel', $order->id), [
            'cancel_reason' => 'Ordered by mistake',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test double cancellation is prevented.
     */
    public function test_double_cancellation_is_prevented(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DBL123',
            'status' => 'cancelled',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
            'cancelled_by' => 'customer',
            'customer_cancel_reason' => 'Ordered by mistake',
        ]);

        $response = $this->actingAs($this->customer1)->post(route('orders.cancel', $order->id), [
            'cancel_reason' => 'Found a better price',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Order is already cancelled.');
    }

    /**
     * Test payment-captured orders cannot be cancelled after confirmation.
     */
    public function test_payment_captured_orders_cannot_be_cancelled_once_processing(): void
    {
        $order = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-PAY123',
            'status' => 'processing',
            'total_amount' => 1000.00,
            'payment_method' => 'card',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($this->customer1)->post(route('orders.cancel', $order->id), [
            'cancel_reason' => 'Ordered by mistake',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test dashboard statistics are updated properly.
     */
    public function test_dashboard_statistics_reflect_cancellation(): void
    {
        // 1. Create a customer cancelled order
        $order1 = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DASH1',
            'status' => 'pending',
            'total_amount' => 1000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $this->actingAs($this->customer1)->post(route('orders.cancel', $order1->id), [
            'cancel_reason' => 'Ordered by mistake',
        ]);

        // 2. Create an admin cancelled order
        $order2 = Order::create([
            'user_id' => $this->customer1->id,
            'order_number' => 'TS-DASH2',
            'status' => 'pending',
            'total_amount' => 1500.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr 1',
            'phone' => '1234567890',
        ]);

        $order2->updateStatus('cancelled', 'Cancelled by stock issues.');

        // Load dashboard
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response->assertViewHas('totalCancelledOverall', 2);
        $response->assertViewHas('customerCancelledOverall', 1);
        $response->assertViewHas('adminCancelledOverall', 1);

        $response->assertViewHas('totalCancelledToday', 2);
        $response->assertViewHas('customerCancelledToday', 1);
        $response->assertViewHas('adminCancelledToday', 1);

        $response->assertViewHas('totalCancelledThisMonth', 2);
        $response->assertViewHas('customerCancelledThisMonth', 1);
        $response->assertViewHas('adminCancelledThisMonth', 1);
    }
}

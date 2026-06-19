<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private User $customer2;
    private Category $category;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin User
        $this->admin = User::create([
            'name' => 'Store Admin',
            'email' => 'admin@tshirt.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Customer Users
        $this->customer = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        $this->customer2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'phone' => '0987654321',
            'address' => '456 Side St',
        ]);

        // Seed Category and Product (Price = 1000.00)
        $this->category = Category::create([
            'name' => 'Anime',
            'slug' => 'anime',
            'description' => 'Anime Tees',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Goku Ultra Instinct Tee',
            'slug' => 'goku-ultra-instinct-tee',
            'description' => 'Power level over 9000.',
            'price' => 1000.00,
            'stock' => 50,
            'sizes' => ['S', 'M', 'L'],
            'colors' => ['Black', 'White'],
            'image_path' => 'images/tshirts/goku.png',
        ]);
    }

    /**
     * Test admin can perform CRUD on coupons.
     */
    public function test_admin_can_crud_coupons(): void
    {
        // 1. List coupons
        $response = $this->actingAs($this->admin)->get(route('admin.coupons.index'));
        $response->assertStatus(200);

        // 2. Create coupon
        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), [
            'code' => 'SUPER50',
            'description' => 'Get 50% off on all items',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'minimum_order_amount' => 500.00,
            'maximum_discount_amount' => 200.00,
            'usage_limit' => 10,
            'usage_per_customer' => 2,
            'start_date' => now()->subDay()->format('Y-m-d H:i:s'),
            'end_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseHas('coupons', ['code' => 'SUPER50']);

        $coupon = Coupon::where('code', 'SUPER50')->first();

        // 3. Edit coupon
        $response = $this->actingAs($this->admin)->get(route('admin.coupons.edit', $coupon->id));
        $response->assertStatus(200);
        $response->assertSee('SUPER50');

        // 4. Update coupon
        $response = $this->actingAs($this->admin)->put(route('admin.coupons.update', $coupon->id), [
            'code' => 'SUPER50_MODIFIED',
            'description' => 'Get 50% off on all items (Updated)',
            'discount_type' => 'percentage',
            'discount_value' => 45.00,
            'minimum_order_amount' => 600.00,
            'maximum_discount_amount' => 150.00,
            'usage_limit' => 12,
            'usage_per_customer' => 3,
            'start_date' => now()->subDay()->format('Y-m-d H:i:s'),
            'end_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'SUPER50_MODIFIED',
            'discount_value' => 45.00,
        ]);

        // 5. View coupon report/show page
        $response = $this->actingAs($this->admin)->get(route('admin.coupons.show', $coupon->id));
        $response->assertStatus(200);
        $response->assertSee('SUPER50_MODIFIED');

        // 6. Delete coupon
        $response = $this->actingAs($this->admin)->delete(route('admin.coupons.destroy', $coupon->id));
        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    /**
     * Test coupon validation conditions.
     */
    public function test_coupon_validation_scenarios(): void
    {
        // 1. Inactive coupon rejection
        $couponInactive = Coupon::create([
            'code' => 'INACTIVE',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'minimum_order_amount' => 0.00,
            'usage_per_customer' => 1,
            'is_active' => false,
        ]);

        // Add item to cart
        $this->actingAs($this->customer)->post(route('cart.add', $this->product->id), [
            'size' => 'M',
            'color' => 'Black',
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'INACTIVE']);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Coupon inactive');
        $this->assertNull(session('coupon'));

        // 2. Future coupon (not started yet) rejection
        $couponFuture = Coupon::create([
            'code' => 'FUTURE',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'minimum_order_amount' => 0.00,
            'usage_per_customer' => 1,
            'start_date' => now()->addDays(2),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'FUTURE']);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Coupon inactive');
        $this->assertNull(session('coupon'));

        // 3. Expired coupon rejection
        $couponExpired = Coupon::create([
            'code' => 'EXPIRED',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'minimum_order_amount' => 0.00,
            'usage_per_customer' => 1,
            'end_date' => now()->subDay(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'EXPIRED']);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Coupon expired');
        $this->assertNull(session('coupon'));

        // 4. Minimum order amount not met
        $couponMinAmt = Coupon::create([
            'code' => 'HIGHMIN',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'minimum_order_amount' => 5000.00, // cart has 1000.00
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'HIGHMIN']);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Minimum order amount not met');
        $this->assertNull(session('coupon'));

        // 5. Total usage limit reached
        $couponUsageLimit = Coupon::create([
            'code' => 'LIMITREACHED',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'minimum_order_amount' => 0.00,
            'usage_limit' => 3,
            'usage_count' => 3,
            'usage_per_customer' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'LIMITREACHED']);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Coupon usage limit reached');
        $this->assertNull(session('coupon'));

        // 6. Per customer usage limit reached
        $couponPerCust = Coupon::create([
            'code' => 'PERCUST',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'minimum_order_amount' => 0.00,
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);

        // Place an order to register a usage
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'TS-USED101',
            'status' => 'pending',
            'total_amount' => 900.00,
            'discount_amount' => 100.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Addr',
            'phone' => '123',
        ]);

        CouponUsage::create([
            'coupon_id' => $couponPerCust->id,
            'user_id' => $this->customer->id,
            'order_id' => $order->id,
            'discount_amount' => 100.00,
            'used_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'PERCUST']);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Coupon usage limit reached for this customer');
        $this->assertNull(session('coupon'));

        // 7. Successful application & removal
        $couponValid = Coupon::create([
            'code' => 'VALID10',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'minimum_order_amount' => 500.00,
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'VALID10']);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Coupon applied successfully.');
        $this->assertEquals(['id' => $couponValid->id, 'code' => 'VALID10'], session('coupon'));

        // Remove coupon
        $response = $this->actingAs($this->customer)->post(route('coupon.remove'));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Coupon removed successfully.');
        $this->assertNull(session('coupon'));
    }

    /**
     * Test discount calculation (fixed and percentage with cap).
     */
    public function test_discount_calculation_logic(): void
    {
        // 1. Percentage discount capped at max discount
        $couponPctCap = Coupon::create([
            'code' => 'PCT50',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'minimum_order_amount' => 0.00,
            'maximum_discount_amount' => 300.00, // 50% of 1000 = 500, but capped at 300
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);

        $this->assertEquals(300.00, $couponPctCap->calculateDiscount(1000.00));
        // No cap check
        $couponPctNoCap = Coupon::create([
            'code' => 'PCT50NOCAP',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'minimum_order_amount' => 0.00,
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);
        $this->assertEquals(500.00, $couponPctNoCap->calculateDiscount(1000.00));

        // 2. Fixed amount discount
        $couponFixed = Coupon::create([
            'code' => 'FIXED150',
            'discount_type' => 'fixed',
            'discount_value' => 150.00,
            'minimum_order_amount' => 0.00,
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);
        $this->assertEquals(150.00, $couponFixed->calculateDiscount(1000.00));

        // 3. Discount cannot exceed subtotal (resulting in negative totals check)
        $couponOver = Coupon::create([
            'code' => 'FREEBIE',
            'discount_type' => 'fixed',
            'discount_value' => 2000.00, // exceeds subtotal of 1000.00
            'minimum_order_amount' => 0.00,
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);
        $this->assertEquals(1000.00, $couponOver->calculateDiscount(1000.00));
    }

    /**
     * Test checkout order creation with coupon discounts.
     */
    public function test_checkout_saves_coupon_and_creates_usage_cod(): void
    {
        // Setup Shipping Setting: Free shipping >= 2500, else 150
        \Illuminate\Support\Facades\DB::table('shipping_settings')->truncate();
        \App\Models\ShippingSetting::create([
            'shipping_fee' => 150.00,
            'free_shipping_min_amount' => 2500.00,
        ]);

        $coupon = Coupon::create([
            'code' => 'SAVE150',
            'discount_type' => 'fixed',
            'discount_value' => 150.00,
            'minimum_order_amount' => 500.00,
            'usage_per_customer' => 2,
            'is_active' => true,
        ]);

        // Add 1 Goku Tee (1000.00) to Cart
        $this->actingAs($this->customer)->post(route('cart.add', $this->product->id), [
            'size' => 'M',
            'color' => 'Black',
            'quantity' => 1,
        ]);

        // Apply coupon
        $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'SAVE150']);
        $this->assertEquals(['id' => $coupon->id, 'code' => 'SAVE150'], session('coupon'));

        // Checkout store
        $response = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'phone' => '1234567890',
            'shipping_address' => '123 Street St',
            'payment_method' => 'cod',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order->id));

        // Subtotal = 1000.00, Discount = 150.00, Shipping = 150.00. Total expected = 1000.00
        $this->assertEquals(1000.00, $order->total_amount);
        $this->assertEquals(150.00, $order->discount_amount);
        $this->assertEquals($coupon->id, $order->coupon_id);
        $this->assertEquals('SAVE150', $order->coupon_code);

        // Verify usage logged
        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'user_id' => $this->customer->id,
            'order_id' => $order->id,
            'discount_amount' => 150.00,
        ]);

        // Verify coupon usage count incremented
        $coupon->refresh();
        $this->assertEquals(1, $coupon->usage_count);

        // Verify session cleared
        $this->assertNull(session('cart'));
        $this->assertNull(session('coupon'));
    }

    /**
     * Test Razorpay payment callback persists coupon state securely.
     */
    public function test_razorpay_checkout_persists_coupon_data(): void
    {
        config(['razorpay.key_secret' => 'test_secret']);

        $coupon = Coupon::create([
            'code' => 'SAVE200',
            'discount_type' => 'fixed',
            'discount_value' => 200.00,
            'minimum_order_amount' => 500.00,
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);

        // Add product to cart
        $this->actingAs($this->customer)->post(route('cart.add', $this->product->id), [
            'size' => 'M',
            'color' => 'Black',
            'quantity' => 1,
        ]);

        // Apply coupon
        $this->actingAs($this->customer)->post(route('coupon.apply'), ['code' => 'SAVE200']);

        // Mock payment verification parameters
        $paymentId = 'pay_test123';
        $orderId = 'order_test456';
        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, 'test_secret');

        $response = $this->actingAs($this->customer)->post(route('checkout.razorpay-callback'), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $orderId,
            'razorpay_signature' => $signature,
            'phone' => '1234567890',
            'shipping_address' => '456 Road Rd',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order->id));

        // Subtotal = 1000.00, Discount = 200.00, Shipping = 150.00. Total expected = 950.00
        $this->assertEquals(950.00, $order->total_amount);
        $this->assertEquals(200.00, $order->discount_amount);
        $this->assertEquals($coupon->id, $order->coupon_id);
        $this->assertEquals('SAVE200', $order->coupon_code);

        // Verify usage log
        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'user_id' => $this->customer->id,
            'order_id' => $order->id,
            'discount_amount' => 200.00,
        ]);

        $coupon->refresh();
        $this->assertEquals(1, $coupon->usage_count);
    }

    /**
     * Test admin dashboard marketing metrics correctness.
     */
    public function test_admin_dashboard_metrics_correctness(): void
    {
        $couponA = Coupon::create([
            'code' => 'COUPONA',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'usage_count' => 5,
            'is_active' => true,
        ]);

        $couponB = Coupon::create([
            'code' => 'COUPONB',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'usage_count' => 12, // top performing
            'is_active' => true,
        ]);

        $couponInactive = Coupon::create([
            'code' => 'INACTIVECOUPON',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'usage_count' => 0,
            'is_active' => false,
        ]);

        // Create some orders with discounts
        Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'TS-ORD1',
            'status' => 'delivered',
            'total_amount' => 950.00,
            'discount_amount' => 50.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr',
            'phone' => '123',
        ]);

        Order::create([
            'user_id' => $this->customer2->id,
            'order_number' => 'TS-ORD2',
            'status' => 'delivered',
            'total_amount' => 900.00,
            'discount_amount' => 100.00,
            'payment_method' => 'cod',
            'payment_status' => 'completed',
            'shipping_address' => 'Addr',
            'phone' => '123',
        ]);

        // Add coupon usages
        CouponUsage::create([
            'coupon_id' => $couponA->id,
            'user_id' => $this->customer->id,
            'order_id' => 1,
            'discount_amount' => 50.00,
        ]);

        CouponUsage::create([
            'coupon_id' => $couponB->id,
            'user_id' => $this->customer2->id,
            'order_id' => 2,
            'discount_amount' => 100.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response->assertViewHas('activeCoupons', 2);
        $response->assertViewHas('totalCouponUsage', 2);
        $response->assertViewHas('discountGiven', 150.00);
        $response->assertViewHas('topPerformingCoupon', 'COUPONB');
    }
}

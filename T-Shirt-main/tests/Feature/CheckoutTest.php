<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Category $category;
    private Product $productLowPrice;
    private Product $productHighPrice;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed a customer
        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'testcustomer@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'phone' => '+1555000000',
            'address' => '456 Test Lane',
        ]);

        // Seed a category
        $this->category = Category::create([
            'name' => 'Oversized',
            'slug' => 'oversized',
            'description' => 'Oversized Tees',
        ]);

        // Seed a low price product (₹999.00)
        $this->productLowPrice = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Cyberpunk Neon Tee',
            'slug' => 'cyberpunk-neon-tee',
            'description' => 'A glowing cyberpunk design.',
            'price' => 999.00,
            'discount_price' => 799.00, // active price: 799.00
            'stock' => 15,
            'sizes' => ['S', 'M', 'L'],
            'colors' => ['Black', 'Navy'],
            'image_path' => 'images/tshirts/cyberpunk.png',
        ]);

        // Seed a high price product (₹3000.00)
        $this->productHighPrice = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Limited Edition Gold Tee',
            'slug' => 'limited-edition-gold-tee',
            'description' => 'Exclusive gold print.',
            'price' => 3000.00,
            'discount_price' => null, // active price: 3000.00
            'stock' => 10,
            'sizes' => ['M', 'L'],
            'colors' => ['Black'],
            'image_path' => 'images/tshirts/retro.png',
        ]);
    }

    /**
     * Test that guests cannot access the checkout page.
     */
    public function test_guest_redirected_from_checkout(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test checkout requires items in the cart.
     */
    public function test_checkout_requires_items_in_cart(): void
    {
        $response = $this->actingAs($this->customer)->get(route('checkout.index'));
        $response->assertRedirect(route('shop.list'));
        $response->assertSessionHas('error', 'Your cart is empty.');
    }

    /**
     * Test placing a COD order with shipping fee (subtotal < ₹2500).
     */
    public function test_can_place_cod_order_with_shipping_fee(): void
    {
        // 1. Add low price product (active price: ₹799.00) to cart
        $cartAddResponse = $this->actingAs($this->customer)
            ->post(route('cart.add', $this->productLowPrice->id), [
                'size' => 'S',
                'color' => 'Black',
                'quantity' => 1,
            ]);

        $cartAddResponse->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('products', [
            'id' => $this->productLowPrice->id,
            'stock' => 15, // Stock not deducted yet
        ]);

        // 2. View Checkout
        $checkoutViewResponse = $this->actingAs($this->customer)->get(route('checkout.index'));
        $checkoutViewResponse->assertStatus(200);

        // 3. Post Checkout Form
        $checkoutSubmitResponse = $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'phone' => '+1555999999',
                'shipping_address' => '789 Shipping Blvd',
                'payment_method' => 'cod',
            ]);

        // Assert order created
        $order = Order::first();
        $this->assertNotNull($order);
        $checkoutSubmitResponse->assertRedirect(route('orders.show', $order->id));

        // Assert order show page renders correctly
        $orderShowResponse = $this->actingAs($this->customer)->get(route('orders.show', $order->id));
        $orderShowResponse->assertStatus(200);

        // Subtotal = 799.00 < 2500, so shipping = 150.00. Total expected = 949.00
        $this->assertEquals(949.00, $order->total_amount);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals('pending', $order->payment_status);

        // Verify stock is decremented
        $this->productLowPrice->refresh();
        $this->assertEquals(14, $this->productLowPrice->stock);

        // Verify cart is cleared
        $this->assertNull(session('cart'));
    }

    /**
     * Test placing a COD order with free shipping (subtotal >= ₹2500).
     */
    public function test_can_place_cod_order_with_free_shipping(): void
    {
        // 1. Add high price product (active price: ₹3000.00) to cart
        $this->actingAs($this->customer)
            ->post(route('cart.add', $this->productHighPrice->id), [
                'size' => 'M',
                'color' => 'Black',
                'quantity' => 1,
            ]);

        // 2. Submit checkout
        $response = $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'phone' => '+1555000001',
                'shipping_address' => '101 Free Shipping St',
                'payment_method' => 'cod',
            ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order->id));

        // Assert order show page renders correctly
        $orderShowResponse = $this->actingAs($this->customer)->get(route('orders.show', $order->id));
        $orderShowResponse->assertStatus(200);

        // Subtotal = 3000.00 >= 2500, so shipping = 0.00. Total expected = 3000.00
        $this->assertEquals(3000.00, $order->total_amount);

        // Verify stock is decremented
        $this->productHighPrice->refresh();
        $this->assertEquals(9, $this->productHighPrice->stock);
    }

    /**
     * Test checkout fails if stock becomes insufficient in the meantime.
     */
    public function test_cannot_checkout_item_exceeding_stock(): void
    {
        // 1. Add 10 items of gold tee (all available stock) to cart
        $this->actingAs($this->customer)
            ->post(route('cart.add', $this->productHighPrice->id), [
                'size' => 'M',
                'color' => 'Black',
                'quantity' => 10,
            ]);

        // 2. Simulate other purchase/admin adjustment that reduces stock in DB to 5
        $this->productHighPrice->update(['stock' => 5]);

        // 3. Attempt Checkout
        $response = $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'phone' => '+1555987654',
                'shipping_address' => 'Some Address',
                'payment_method' => 'cod',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('out of stock or does not have enough inventory', session('error'));

        // Assert order was NOT created
        $this->assertEquals(0, Order::count());
    }

    /**
     * Test submitting a review for a product.
     */
    public function test_submit_review_for_product(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('reviews.store', $this->productLowPrice->id), [
                'rating' => 5,
                'comment' => 'This Cyberpunk Neon Tee is absolutely amazing! Quality is top-notch.',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->customer->id,
            'product_id' => $this->productLowPrice->id,
            'rating' => 5,
            'comment' => 'This Cyberpunk Neon Tee is absolutely amazing! Quality is top-notch.',
        ]);
    }
}

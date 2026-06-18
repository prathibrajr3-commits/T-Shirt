<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

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

        // Customer User
        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);
    }

    /**
     * Guests cannot view admin banners.
     */
    public function test_guest_cannot_access_banners(): void
    {
        $response = $this->get(route('admin.banners.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Regular customers cannot view admin banners.
     */
    public function test_customer_cannot_access_banners(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.banners.index'));
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Unauthorized access.');
    }

    /**
     * Admins can view admin banners index.
     */
    public function test_admin_can_access_banners_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.banners.index'));
        $response->assertStatus(200);
        $response->assertViewHas('banners');
    }

    /**
     * Admins can create a banner.
     */
    public function test_admin_can_create_banner(): void
    {
        // Mock image file
        $file = UploadedFile::fake()->image('banner1.jpg', 1920, 600);

        $response = $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'title' => 'New Arrival Slider',
            'subtitle' => 'Special discount active',
            'button_text' => 'Shop Collection',
            'button_link' => '/shop',
            'image' => $file,
            'order_position' => 1,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('banners', [
            'title' => 'New Arrival Slider',
            'subtitle' => 'Special discount active',
            'button_text' => 'Shop Collection',
            'button_link' => '/shop',
            'order_position' => 1,
            'is_active' => true,
        ]);

        $banner = Banner::first();
        $this->assertNotNull($banner);
        $this->assertTrue(file_exists(public_path($banner->image_path)));

        // Clean up mock file
        @unlink(public_path($banner->image_path));
    }

    /**
     * Admins can update a banner.
     */
    public function test_admin_can_update_banner(): void
    {
        $banner = Banner::create([
            'title' => 'Initial Title',
            'subtitle' => 'Initial Subtitle',
            'button_text' => 'Click',
            'button_link' => '/shop',
            'image_path' => 'images/banners/cyberpunk.png',
            'order_position' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.banners.update', $banner->id), [
            'title' => 'Updated Title',
            'subtitle' => 'Updated Subtitle',
            'button_text' => 'Click Updated',
            'button_link' => '/shop/updated',
            'order_position' => 5,
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'title' => 'Updated Title',
            'subtitle' => 'Updated Subtitle',
            'button_text' => 'Click Updated',
            'button_link' => '/shop/updated',
            'order_position' => 5,
            'is_active' => false, // unchecked is_active defaults to false
        ]);
    }

    /**
     * Admins can delete a banner.
     */
    public function test_admin_can_delete_banner(): void
    {
        $banner = Banner::create([
            'title' => 'Temporary Banner',
            'button_text' => 'Delete Me',
            'button_link' => '/home',
            'image_path' => 'images/banners/cyberpunk.png',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.banners.destroy', $banner->id));

        $response->assertRedirect(route('admin.banners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('banners', [
            'id' => $banner->id,
        ]);
    }
}

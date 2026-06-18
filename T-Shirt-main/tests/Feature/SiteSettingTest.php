<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\FooterLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSettingTest extends TestCase
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
     * Guest redirect.
     */
    public function test_guest_cannot_access_site_settings(): void
    {
        $response = $this->get(route('admin.site-settings.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Customer redirect.
     */
    public function test_customer_cannot_access_site_settings(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.site-settings.index'));
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Unauthorized access.');
    }

    /**
     * Admin can view dashboard.
     */
    public function test_admin_can_access_site_settings_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.site-settings.index'));
        $response->assertStatus(200);
        $response->assertViewHasAll(['settings', 'links']);
    }

    /**
     * Admin can update site settings.
     */
    public function test_admin_can_update_site_settings(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.site-settings.update'), [
            'store_name' => 'My Brand',
            'admin_header_title' => 'My Brand Admin',
            'tagline' => 'Brand tagline.',
            'footer_title' => 'My Brand Footer',
            'footer_description' => 'Brand description detail.',
            'copyright_text' => '© 2026 My Brand',
            'email' => 'contact@mybrand.com',
            'phone' => '+1 (555) 987-6543',
            'address' => '123 Brand St, NY',
            'facebook_url' => 'https://facebook.com/mybrand',
            'instagram_url' => 'https://instagram.com/mybrand',
            'twitter_url' => 'https://twitter.com/mybrand',
            'show_store_logo' => '1',
            'show_store_name' => '1',
            'show_footer_logo' => '1',
            'show_footer_contact' => '1',
            'show_footer_social' => '1',
        ]);

        $response->assertRedirect(route('admin.site-settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('site_settings', [
            'store_name' => 'My Brand',
            'admin_header_title' => 'My Brand Admin',
            'tagline' => 'Brand tagline.',
            'footer_title' => 'My Brand Footer',
            'footer_description' => 'Brand description detail.',
            'copyright_text' => '© 2026 My Brand',
            'email' => 'contact@mybrand.com',
            'phone' => '+1 (555) 987-6543',
            'address' => '123 Brand St, NY',
            'facebook_url' => 'https://facebook.com/mybrand',
            'instagram_url' => 'https://instagram.com/mybrand',
            'twitter_url' => 'https://twitter.com/mybrand',
            'show_store_logo' => true,
            'show_store_name' => true,
            'show_footer_logo' => true,
            'show_footer_contact' => true,
            'show_footer_social' => true,
        ]);
    }

    /**
     * Logo image upload validation.
     */
    public function test_logo_validation_enforces_rules(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('large_logo.jpg', 3000, 'image/jpeg'); // Too large (3MB)

        $response = $this->actingAs($this->admin)->put(route('admin.site-settings.update'), [
            'store_name' => 'Brand',
            'store_logo' => $file,
        ]);

        $response->assertSessionHasErrors(['store_logo']);
    }

    /**
     * Favicon file upload validation.
     */
    public function test_favicon_validation_enforces_rules(): void
    {
        Storage::fake('public');
        
        // 1. Invalid Format (txt)
        $fileTxt = UploadedFile::fake()->create('favicon.txt', 10, 'text/plain');
        $response = $this->actingAs($this->admin)->put(route('admin.site-settings.update'), [
            'store_name' => 'Brand',
            'favicon' => $fileTxt,
        ]);
        $response->assertSessionHasErrors([
            'favicon' => 'Only PNG and ICO files are allowed.'
        ]);

        // 2. Too large (2500KB > 2048KB)
        $fileLarge = UploadedFile::fake()->create('favicon.png', 2500, 'image/png');
        $response2 = $this->actingAs($this->admin)->put(route('admin.site-settings.update'), [
            'store_name' => 'Brand',
            'favicon' => $fileLarge,
        ]);
        $response2->assertSessionHasErrors([
            'favicon' => 'Favicon must not exceed 2 MB.'
        ]);
    }

    /**
     * Logo and favicon upload success.
     */
    public function test_logo_and_favicon_upload_success(): void
    {
        Storage::fake('public');
        $logo = UploadedFile::fake()->image('store_logo.png');
        $faviconPng = UploadedFile::fake()->create('favicon.png', 1500, 'image/png');

        $response = $this->actingAs($this->admin)->put(route('admin.site-settings.update'), [
            'store_name' => 'Brand',
            'store_logo' => $logo,
            'favicon' => $faviconPng,
        ]);

        $response->assertRedirect(route('admin.site-settings.index'));
        
        $setting = SiteSetting::first();
        $this->assertNotNull($setting->store_logo);
        $this->assertNotNull($setting->favicon);
        
        Storage::disk('public')->assertExists($setting->store_logo);
        Storage::disk('public')->assertExists($setting->favicon);
    }

    /**
     * ICO favicon upload success.
     */
    public function test_ico_favicon_upload_success(): void
    {
        Storage::fake('public');
        $faviconIco = UploadedFile::fake()->create('favicon.ico', 32, 'image/x-icon');

        $response = $this->actingAs($this->admin)->put(route('admin.site-settings.update'), [
            'store_name' => 'Brand',
            'favicon' => $faviconIco,
        ]);

        $response->assertRedirect(route('admin.site-settings.index'));
        
        $setting = SiteSetting::first();
        $this->assertNotNull($setting->favicon);
        Storage::disk('public')->assertExists($setting->favicon);
    }

    /**
     * Admin can add and update quick links.
     */
    public function test_admin_can_manage_links(): void
    {
        // Add Link
        $response = $this->actingAs($this->admin)->post(route('admin.site-settings.links.store'), [
            'text' => 'Privacy Policy',
            'url' => '/privacy',
        ]);
        $response->assertRedirect(route('admin.site-settings.index'));
        $this->assertDatabaseHas('footer_links', [
            'text' => 'Privacy Policy',
            'url' => '/privacy',
        ]);

        $link = FooterLink::first();

        // Update Link
        $response = $this->actingAs($this->admin)->put(route('admin.site-settings.links.update', $link->id), [
            'text' => 'Privacy Updated',
            'url' => '/privacy-policy',
        ]);
        $response->assertRedirect(route('admin.site-settings.index'));
        $this->assertDatabaseHas('footer_links', [
            'id' => $link->id,
            'text' => 'Privacy Updated',
            'url' => '/privacy-policy',
        ]);

        // Delete Link
        $response = $this->actingAs($this->admin)->delete(route('admin.site-settings.links.destroy', $link->id));
        $response->assertRedirect(route('admin.site-settings.index'));
        $this->assertDatabaseMissing('footer_links', [
            'id' => $link->id,
        ]);
    }

    /**
     * AJAX Reorder links test.
     */
    public function test_ajax_reorder_links_saves_to_database(): void
    {
        $link1 = FooterLink::create(['text' => 'Link 1', 'url' => '/1', 'sort_order' => 0]);
        $link2 = FooterLink::create(['text' => 'Link 2', 'url' => '/2', 'sort_order' => 1]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.site-settings.links.reorder'), [
            'ids' => [$link2->id, $link1->id]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $link1->refresh();
        $link2->refresh();

        $this->assertEquals(1, $link1->sort_order);
        $this->assertEquals(0, $link2->sort_order);
    }

    /**
     * Storefront and Layout rendering.
     */
    public function test_storefront_renders_dynamic_site_settings(): void
    {
        $setting = SiteSetting::updateOrCreate(['id' => 1], [
            'store_name' => 'DYNAMIC STORE',
            'tagline' => 'Dynamic Tagline',
            'footer_title' => 'DYNAMIC FOOTER TITLE',
            'footer_description' => 'Dynamic Footer Desc',
            'copyright_text' => '© 2026 Dynamic Inc.',
            'email' => 'dynamic@example.com',
            'phone' => '+1 555 999 9999',
            'address' => 'Dynamic Address',
            'facebook_url' => 'https://facebook.com/dynamic',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify title
        $response->assertSee('DYNAMIC STORE');
        $response->assertSee('Dynamic Tagline');

        // Verify footer title and desc
        $response->assertSee('DYNAMIC FOOTER TITLE');
        $response->assertSee('Dynamic Footer Desc');
        $response->assertSee('© 2026 Dynamic Inc.');

        // Verify contacts
        $response->assertSee('dynamic@example.com');
        $response->assertSee('+1 555 999 9999');
        $response->assertSee('Dynamic Address');

        // Verify social url
        $response->assertSee('https://facebook.com/dynamic');
    }
}

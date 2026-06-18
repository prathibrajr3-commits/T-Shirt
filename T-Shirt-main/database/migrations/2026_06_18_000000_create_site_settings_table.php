<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // Existing Store Settings
            $table->string('store_name')->nullable();
            $table->string('admin_header_title')->nullable();
            $table->string('tagline')->nullable();

            // Branding
            $table->string('store_logo')->nullable();
            $table->string('favicon')->nullable();

            // Display Options
            $table->boolean('show_store_logo')->default(true);
            $table->boolean('show_store_name')->default(true);
            $table->boolean('show_footer_logo')->default(true);
            $table->boolean('show_footer_social')->default(true);
            $table->boolean('show_footer_contact')->default(true);

            // Footer Information
            $table->string('footer_title')->nullable();
            $table->text('footer_description')->nullable();
            $table->string('copyright_text')->nullable();

            // Contact Information
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('whatsapp_url')->nullable();

            $table->timestamps();
        });

        Schema::create('footer_links', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('url');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Migrate existing data if tables exist
        $siteSettingsData = [
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // 1. Store settings
        if (Schema::hasTable('store_settings')) {
            $store = DB::table('store_settings')->first();
            if ($store) {
                $siteSettingsData['store_name'] = $store->store_name;
                $siteSettingsData['admin_header_title'] = $store->admin_header_title;
                $siteSettingsData['tagline'] = $store->tagline;
                $siteSettingsData['store_logo'] = $store->store_logo;
                $siteSettingsData['favicon'] = $store->favicon;
                $siteSettingsData['show_store_logo'] = $store->show_logo;
                $siteSettingsData['show_store_name'] = $store->show_text;
                $siteSettingsData['phone'] = $store->phone;
                $siteSettingsData['email'] = $store->email;
                $siteSettingsData['address'] = $store->address;
            }
        }

        // 2. Footer settings (overwrite if exists / merge)
        if (Schema::hasTable('footer_settings')) {
            $footer = DB::table('footer_settings')->first();
            if ($footer) {
                $siteSettingsData['footer_title'] = $footer->brand_name ?? $siteSettingsData['store_name'] ?? null;
                $siteSettingsData['footer_description'] = $footer->description;
                $siteSettingsData['copyright_text'] = $footer->copyright_text;
                $siteSettingsData['show_footer_logo'] = $footer->show_about_section;
                $siteSettingsData['show_footer_contact'] = $footer->show_contact_section;
                $siteSettingsData['show_footer_social'] = $footer->show_social_section;
                
                // If logo/favicon wasn't in store_settings but exists in footer_settings, migrate it
                if (empty($siteSettingsData['store_logo']) && !empty($footer->logo)) {
                    $siteSettingsData['store_logo'] = $footer->logo;
                }
                if (empty($siteSettingsData['favicon']) && !empty($footer->favicon)) {
                    $siteSettingsData['favicon'] = $footer->favicon;
                }
            }
        }

        // 3. Footer contacts (merge into email, phone, address if they were empty)
        if (Schema::hasTable('footer_contacts')) {
            $contacts = DB::table('footer_contacts')->get();
            foreach ($contacts as $contact) {
                if ($contact->icon === 'envelope' && empty($siteSettingsData['email'])) {
                    $siteSettingsData['email'] = $contact->value;
                } elseif (($contact->icon === 'phone' || $contact->icon === 'headset') && empty($siteSettingsData['phone'])) {
                    $siteSettingsData['phone'] = $contact->value;
                } elseif ($contact->icon === 'location-dot' && empty($siteSettingsData['address'])) {
                    $siteSettingsData['address'] = $contact->value;
                }
            }
        }

        // 4. Social links (migrate values to correct columns)
        if (Schema::hasTable('social_links')) {
            $socials = DB::table('social_links')->get();
            foreach ($socials as $social) {
                if ($social->is_enabled) {
                    $plat = strtolower($social->platform);
                    if (str_contains($plat, 'facebook')) {
                        $siteSettingsData['facebook_url'] = $social->url;
                    } elseif (str_contains($plat, 'instagram')) {
                        $siteSettingsData['instagram_url'] = $social->url;
                    } elseif (str_contains($plat, 'twitter') || str_contains($plat, 'x')) {
                        $siteSettingsData['twitter_url'] = $social->url;
                    } elseif (str_contains($plat, 'youtube')) {
                        $siteSettingsData['youtube_url'] = $social->url;
                    } elseif (str_contains($plat, 'linkedin')) {
                        $siteSettingsData['linkedin_url'] = $social->url;
                    } elseif (str_contains($plat, 'whatsapp')) {
                        $siteSettingsData['whatsapp_url'] = $social->url;
                    }
                }
            }
        }

        // 5. Ensure default values if everything is empty
        if (empty($siteSettingsData['store_name'])) {
            $siteSettingsData['store_name'] = 'AURAWEAR';
        }
        if (empty($siteSettingsData['admin_header_title'])) {
            $siteSettingsData['admin_header_title'] = 'AURA ADMIN';
        }

        // Insert or update record
        DB::table('site_settings')->updateOrInsert(['id' => 1], $siteSettingsData);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('footer_links');
    }
};

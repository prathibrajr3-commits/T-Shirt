<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin User
        User::create([
            'name' => 'Store Admin',
            'email' => 'admin@tshirt.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '+1555123456',
            'address' => 'Admin HQ Suite 100',
        ]);

        // Seed a sample customer user for easy login/testing
        User::create([
            'name' => 'John Doe',
            'email' => 'customer@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('customer123'),
            'role' => 'customer',
            'phone' => '+1555987654',
            'address' => '123 Customer St, Shopping Town',
        ]);

        // 2. Seed Categories
        $men = \App\Models\Category::create([
            'name' => 'Men',
            'slug' => 'men',
            'description' => 'Classic and modern t-shirts for men.',
        ]);

        $women = \App\Models\Category::create([
            'name' => 'Women',
            'slug' => 'women',
            'description' => 'Premium crop tops and fitted t-shirts for women.',
        ]);

        $oversized = \App\Models\Category::create([
            'name' => 'Oversized',
            'slug' => 'oversized',
            'description' => 'Baggy, comfortable, and trendy oversized tees.',
        ]);

        $printed = \App\Models\Category::create([
            'name' => 'Printed',
            'slug' => 'printed',
            'description' => 'Creative graphic and artistic prints.',
        ]);

        // 3. Seed Products
        \App\Models\Product::create([
            'category_id' => $oversized->id,
            'name' => 'Cyberpunk Neon Tee',
            'slug' => 'cyberpunk-neon-tee',
            'description' => 'A heavy-weight cotton oversized streetwear t-shirt featuring a stunning, glowing neon cyberpunk design printed on the chest. Combines durability with futuristic design accents.',
            'price' => 999.00,
            'discount_price' => 799.00,
            'stock' => 15,
            'sizes' => ['S', 'M', 'L', 'XL'],
            'colors' => ['Black', 'Navy'],
            'image_path' => 'images/tshirts/cyberpunk.png',
        ]);

        \App\Models\Product::create([
            'category_id' => $men->id,
            'name' => 'Minimalist Forest Tee',
            'slug' => 'minimalist-forest-tee',
            'description' => 'An elegant off-white t-shirt featuring a tiny, beautifully embroidered pine forest design on the left pocket region. Made from 100% organic cotton.',
            'price' => 699.00,
            'discount_price' => null,
            'stock' => 20,
            'sizes' => ['M', 'L', 'XL'],
            'colors' => ['White', 'Green'],
            'image_path' => 'images/tshirts/minimalist.png',
        ]);

        \App\Models\Product::create([
            'category_id' => $printed->id,
            'name' => 'Retro Synthwave Sunset',
            'slug' => 'retro-synthwave-sunset',
            'description' => 'Travel back to the 80s with this premium navy blue tee showcasing a vibrant, distressed synthwave sunset grid graphic print.',
            'price' => 899.00,
            'discount_price' => 699.00,
            'stock' => 8,
            'sizes' => ['S', 'M', 'L'],
            'colors' => ['Navy', 'Black'],
            'image_path' => 'images/tshirts/retro.png',
        ]);

        \App\Models\Product::create([
            'category_id' => $men->id,
            'name' => 'Classic White Cotton Tee',
            'slug' => 'classic-white-cotton-tee',
            'description' => 'The essential everyday t-shirt. Soft, durable, lightweight, and engineered for maximum breathability.',
            'price' => 499.00,
            'discount_price' => null,
            'stock' => 50,
            'sizes' => ['S', 'M', 'L', 'XL'],
            'colors' => ['White'],
            'image_path' => 'images/tshirts/minimalist.png',
        ]);

        \App\Models\Product::create([
            'category_id' => $printed->id,
            'name' => 'Anime Streetwear Tee',
            'slug' => 'anime-streetwear-tee',
            'description' => 'A bold white t-shirt featuring a highly-detailed anime illustration graphic printed on the back. Perfect for anime enthusiasts and street style lovers.',
            'price' => 1199.00,
            'discount_price' => 999.00,
            'stock' => 12,
            'sizes' => ['M', 'L', 'XL'],
            'colors' => ['White', 'Black'],
            'image_path' => 'images/tshirts/anime.png',
        ]);

        \App\Models\Product::create([
            'category_id' => $oversized->id,
            'name' => 'Oversized Vintage Violet',
            'slug' => 'oversized-vintage-violet',
            'description' => 'Exquisite vintage faded violet tee. Thick ribbed collar, double-needle stitching, and a relaxed, heavy silhouette.',
            'price' => 1299.00,
            'discount_price' => null,
            'stock' => 5,
            'sizes' => ['S', 'M', 'L'],
            'colors' => ['Violet'],
            'image_path' => 'images/tshirts/cyberpunk.png',
        ]);

        \App\Models\Product::create([
            'category_id' => $women->id,
            'name' => "Women's Pastel Crop Tee",
            'slug' => 'womens-pastel-crop-tee',
            'description' => 'A chic pastel pink crop tee. Light-weight and matches perfectly with high-waisted jeans.',
            'price' => 599.00,
            'discount_price' => 499.00,
            'stock' => 25,
            'sizes' => ['S', 'M', 'L'],
            'colors' => ['Pink', 'White'],
            'image_path' => 'images/tshirts/minimalist.png',
        ]);

        \App\Models\Product::create([
            'category_id' => $women->id,
            'name' => "Women's Graphic Print Tee",
            'slug' => 'womens-graphic-print-tee',
            'description' => 'Fitted black tee featuring an artistic line art print. Elegant, versatile, and premium quality cotton.',
            'price' => 699.00,
            'discount_price' => null,
            'stock' => 18,
            'sizes' => ['S', 'M', 'L'],
            'colors' => ['Black', 'White'],
            'image_path' => 'images/tshirts/retro.png',
        ]);

        // 4. Seed Banners
        $bannerDir = public_path('images/banners');
        if (!file_exists($bannerDir)) {
            mkdir($bannerDir, 0755, true);
        }

        $imagesToCopy = [
            'cyberpunk.png',
            'retro.png',
            'minimalist.png'
        ];

        foreach ($imagesToCopy as $img) {
            $src = public_path('images/tshirts/' . $img);
            $dest = $bannerDir . '/' . $img;
            if (file_exists($src) && !file_exists($dest)) {
                copy($src, $dest);
            }
        }

        \App\Models\Banner::create([
            'title' => 'New Oversized T-Shirts Collection',
            'subtitle' => 'Trendy Styles Starting From ₹499',
            'button_text' => 'Shop Now',
            'button_link' => '/shop?category=oversized',
            'image_path' => 'images/banners/cyberpunk.png',
            'is_active' => true,
            'order_position' => 1,
        ]);

        \App\Models\Banner::create([
            'title' => 'Flat 40% OFF on Printed T-Shirts',
            'subtitle' => 'Limited Time Offer',
            'button_text' => 'Explore Collection',
            'button_link' => '/shop?category=printed',
            'image_path' => 'images/banners/retro.png',
            'is_active' => true,
            'order_position' => 2,
        ]);

        \App\Models\Banner::create([
            'title' => 'Premium Quality Cotton Wear',
            'subtitle' => 'Comfort Meets Fashion',
            'button_text' => 'Buy Now',
            'button_link' => '/shop?category=men',
            'image_path' => 'images/banners/minimalist.png',
            'is_active' => true,
            'order_position' => 3,
        ]);

        // 5. Seed Footer Settings
        // 5. Seed Site Settings
        \App\Models\SiteSetting::create([
            'store_name' => 'AURAWEAR',
            'admin_header_title' => 'AURA ADMIN',
            'tagline' => 'Premium T-Shirt E-Commerce',
            'show_store_logo' => true,
            'show_store_name' => true,
            'show_footer_logo' => true,
            'show_footer_contact' => true,
            'show_footer_social' => true,
            'footer_title' => 'AURAWEAR',
            'footer_description' => 'High-quality cyberpunk, minimalist, retro, and printed street style t-shirts. Designed for durability and styled for comfort.',
            'copyright_text' => '© ' . date('Y') . ' AuraWear Inc. All rights reserved.',
            'phone' => '+1 (555) 123-4567',
            'email' => 'support@aurawear.com',
            'address' => 'Fashion District, NY',
            'facebook_url' => 'https://facebook.com',
            'instagram_url' => 'https://instagram.com',
            'twitter_url' => 'https://twitter.com',
            'youtube_url' => null,
            'linkedin_url' => null,
            'whatsapp_url' => null,
        ]);

        // 6. Seed Footer Links
        \App\Models\FooterLink::create(['text' => 'Home', 'url' => '/', 'sort_order' => 1]);
        \App\Models\FooterLink::create(['text' => 'Shop', 'url' => '/shop', 'sort_order' => 2]);
        \App\Models\FooterLink::create(['text' => 'Cart', 'url' => '/cart', 'sort_order' => 3]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the categories table has at least one category for foreign key constraints.
        $categoryId = DB::table('categories')->first()?->id ?? DB::table('categories')->insertGetId([
            'name' => 'Sample Category',
            'slug' => 'sample-category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Product::create([
            'category_id' => $categoryId,
            'name' => 'Sample T-Shirt',
            'slug' => 'sample-tshirt',
            'description' => 'A premium sample T‑shirt for testing.',
            'price' => 1999.00,
            'discount_price' => null,
            'stock' => 50,
            'sizes' => ['S', 'M', 'L', 'XL'],
            'colors' => ['black', 'white'],
            'image_path' => 'products/sample.jpg',
            // 'is_active' column removed as not present in schema
        ]);
    }
}

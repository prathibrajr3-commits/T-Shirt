<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('estimated_shipping', 10, 2)->default(150.00);
            $table->decimal('free_shipping_minimum', 10, 2)->default(2500.00);
            $table->enum('shipping_type', ['fixed', 'free_above_threshold'])->default('free_above_threshold');
            $table->string('custom_message')->default('Add :amount more for FREE shipping!');
            $table->string('free_shipping_message')->default('🎉 You\'ve unlocked FREE shipping!');
            $table->string('icon_class')->default('fa-solid fa-truck-fast');
            $table->string('background_color')->default('transparent');
            $table->string('text_color')->default('#ffffff');
            $table->string('border_radius')->default('0.5rem');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_free_shipping_promo')->default(true);
            $table->timestamps();
        });

        // Insert default record
        DB::table('shipping_settings')->insert([
            'estimated_shipping'    => 150.00,
            'free_shipping_minimum' => 2500.00,
            'shipping_type'         => 'free_above_threshold',
            'custom_message'        => 'Add :amount more for FREE shipping!',
            'free_shipping_message' => '🎉 You\'ve unlocked FREE shipping!',
            'icon_class'            => 'fa-solid fa-truck-fast',
            'background_color'      => 'transparent',
            'text_color'            => '#ffffff',
            'border_radius'         => '0.5rem',
            'is_active'             => true,
            'show_free_shipping_promo' => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};

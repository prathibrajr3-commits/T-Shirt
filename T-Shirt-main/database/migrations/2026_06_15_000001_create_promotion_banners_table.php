<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotion_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('LIMITED TIME OFFER');
            $table->string('heading');
            $table->text('subtitle')->nullable();
            $table->string('coupon_code')->nullable();
            $table->string('button_text')->default('Shop Now');
            $table->string('button_link')->default('/shop');
            $table->string('background_image')->nullable();
            $table->string('gradient_color_1')->default('#8b5cf6');
            $table->string('gradient_color_2')->default('#3b82f6');
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_banners');
    }
};

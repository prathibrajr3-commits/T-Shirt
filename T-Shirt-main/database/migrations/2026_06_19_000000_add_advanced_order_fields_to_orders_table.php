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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_provider')->nullable()->after('tracking_number');
            $table->string('tracking_url')->nullable()->after('shipping_provider');
            $table->timestamp('shipped_at')->nullable()->after('tracking_url');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
            $table->timestamp('status_changed_at')->nullable()->after('cancelled_at');
            $table->text('notes')->nullable()->after('status_changed_at');

            // Add proper indexes
            $table->index('status');
            $table->index('tracking_number');
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['tracking_number']);
            $table->dropIndex(['order_number']);

            $table->dropColumn([
                'shipping_provider',
                'tracking_url',
                'shipped_at',
                'delivered_at',
                'cancelled_at',
                'status_changed_at',
                'notes',
            ]);
        });
    }
};

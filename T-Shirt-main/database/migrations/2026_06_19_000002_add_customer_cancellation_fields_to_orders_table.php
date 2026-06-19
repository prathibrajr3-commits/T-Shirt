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
            $table->text('customer_cancel_reason')->nullable()->after('notes');
            $table->timestamp('customer_cancelled_at')->nullable()->after('customer_cancel_reason');
            $table->string('cancelled_by')->nullable()->after('customer_cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_cancel_reason',
                'customer_cancelled_at',
                'cancelled_by',
            ]);
        });
    }
};

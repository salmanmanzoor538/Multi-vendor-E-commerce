<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at', 'orders_created_at_idx');
            $table->index(['vendor_id', 'created_at'], 'orders_vendor_created_at_idx');
            $table->index(['checkout_batch_id', 'created_at'], 'orders_checkout_batch_created_at_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'paid_at'], 'payments_status_paid_at_idx');
            $table->index(['checkout_batch_id', 'status', 'paid_at'], 'payments_checkout_status_paid_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_checkout_status_paid_at_idx');
            $table->dropIndex('payments_status_paid_at_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_checkout_batch_created_at_idx');
            $table->dropIndex('orders_vendor_created_at_idx');
            $table->dropIndex('orders_created_at_idx');
        });
    }
};

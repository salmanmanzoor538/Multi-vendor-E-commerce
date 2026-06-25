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
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'products_status_created_at_idx');
            $table->index(['vendor_id', 'created_at'], 'products_vendor_created_at_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'vendors_status_created_at_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'orders_user_created_at_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['vendor_id', 'status', 'created_at'], 'reviews_vendor_status_created_at_idx');
            $table->index(['status', 'created_at'], 'reviews_status_created_at_idx');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['vendor_id', 'status', 'ends_at'], 'subscriptions_vendor_status_ends_at_idx');
        });

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->index(['order_id', 'created_at'], 'order_status_histories_order_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->dropIndex('order_status_histories_order_created_at_idx');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_vendor_status_ends_at_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_status_created_at_idx');
            $table->dropIndex('reviews_vendor_status_created_at_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_created_at_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('vendors_status_created_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_vendor_created_at_idx');
            $table->dropIndex('products_status_created_at_idx');
        });
    }
};


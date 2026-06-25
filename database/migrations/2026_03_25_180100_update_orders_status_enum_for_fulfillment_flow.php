<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')->where('status', 'paid')->update(['status' => 'confirmed']);
        DB::table('orders')->where('status', 'failed')->update(['status' => 'cancelled']);

        DB::statement("
            ALTER TABLE `orders`
            MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled')
            NOT NULL DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')->where('status', 'processing')->update(['status' => 'confirmed']);
        DB::table('orders')->where('status', 'shipped')->update(['status' => 'paid']);
        DB::table('orders')->where('status', 'delivered')->update(['status' => 'paid']);

        DB::statement("
            ALTER TABLE `orders`
            MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'paid', 'cancelled', 'failed')
            NOT NULL DEFAULT 'pending'
        ");
    }
};

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
        Schema::table('product_images', function (Blueprint $table) {
            if (! Schema::hasColumn('product_images', 'size_bytes')) {
                $table->unsignedBigInteger('size_bytes')->default(0)->after('path');
                $table->index('size_bytes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (Schema::hasColumn('product_images', 'size_bytes')) {
                $table->dropIndex(['size_bytes']);
                $table->dropColumn('size_bytes');
            }
        });
    }
};

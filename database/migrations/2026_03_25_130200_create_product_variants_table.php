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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('sku');
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->string('combination_key', 500);
            $table->json('option_snapshot')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'combination_key']);
            $table->unique(['vendor_id', 'sku']);
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

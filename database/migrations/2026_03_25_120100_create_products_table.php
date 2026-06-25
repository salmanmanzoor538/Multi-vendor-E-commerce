<?php

use App\Enums\ProductStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('sku');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->enum('status', array_column(ProductStatus::cases(), 'value'))->default(ProductStatus::DRAFT->value);
            $table->timestamps();

            $table->unique(['vendor_id', 'sku']);
            $table->index('vendor_id');
            $table->index('status');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

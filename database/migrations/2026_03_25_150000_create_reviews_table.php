<?php

use App\Enums\ReviewStatus;
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->enum('status', array_column(ReviewStatus::cases(), 'value'))->default(ReviewStatus::PENDING->value);
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_note')->nullable();
            $table->text('vendor_reply')->nullable();
            $table->timestamp('vendor_replied_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

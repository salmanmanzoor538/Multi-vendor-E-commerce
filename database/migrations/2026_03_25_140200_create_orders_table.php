<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('checkout_batch_id');
            $table->string('order_number', 40)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->string('payment_provider', 30)->nullable();
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', array_column(PaymentStatus::cases(), 'value'))->default(PaymentStatus::PENDING->value);
            $table->enum('status', array_column(OrderStatus::cases(), 'value'))->default(OrderStatus::PENDING->value);
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->json('shipping_address_snapshot');
            $table->json('billing_address_snapshot');
            $table->timestamps();

            $table->index('checkout_batch_id');
            $table->index(['user_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index('payment_reference');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

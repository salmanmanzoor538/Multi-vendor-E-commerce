<?php

use App\Enums\PaymentProvider;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('checkout_batch_id')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('provider', array_column(PaymentProvider::cases(), 'value'));
            $table->enum('status', array_column(PaymentStatus::cases(), 'value'))->default(PaymentStatus::PENDING->value);
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('gateway_reference')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_event_id')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index('stripe_session_id');
            $table->index('stripe_payment_intent_id');
            $table->index('gateway_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

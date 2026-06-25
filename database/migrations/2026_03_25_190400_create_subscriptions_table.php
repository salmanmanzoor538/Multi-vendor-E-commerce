<?php

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->enum('billing_cycle', array_column(BillingCycle::cases(), 'value'));
            $table->enum('status', array_column(SubscriptionStatus::cases(), 'value'))->default(SubscriptionStatus::ACTIVE->value);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('status');
            $table->index('ends_at');
            $table->index(['vendor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

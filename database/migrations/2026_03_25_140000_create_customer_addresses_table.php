<?php

use App\Enums\AddressType;
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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', array_column(AddressType::cases(), 'value'));
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('line_1');
            $table->string('line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code', 40);
            $table->string('country_code', 2);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'type', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};

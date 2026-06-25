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
        if (! Schema::hasColumn('users', 'vendor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->after('role')->index()->constrained('vendors')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'vendor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vendor_id');
            });
        }
    }
};

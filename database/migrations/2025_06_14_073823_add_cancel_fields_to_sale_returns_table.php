<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            // Add cancellation tracking fields
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('processed_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');

            // Update status enum to include cancelled
            $table->enum('status', ['pending', 'approved', 'processed', 'rejected', 'cancelled'])->default('pending')->change();
        });
    }

    public function down()
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_by', 'cancelled_at']);

            // Revert status enum
            $table->enum('status', ['pending', 'approved', 'processed', 'rejected'])->default('pending')->change();
        });
    }
};

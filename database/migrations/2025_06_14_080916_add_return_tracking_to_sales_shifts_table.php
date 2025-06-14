<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales_shifts', function (Blueprint $table) {
            // Add return tracking columns
            $table->integer('total_returns_count')->default(0)->after('total_transactions');
            $table->decimal('total_returns_amount', 12, 2)->default(0)->after('total_returns_count');
            $table->integer('processed_returns_count')->default(0)->after('total_returns_amount');
            $table->decimal('processed_returns_amount', 12, 2)->default(0)->after('processed_returns_count');
        });
    }

    public function down()
    {
        Schema::table('sales_shifts', function (Blueprint $table) {
            $table->dropColumn([
                'total_returns_count',
                'total_returns_amount',
                'processed_returns_count',
                'processed_returns_amount'
            ]);
        });
    }
};

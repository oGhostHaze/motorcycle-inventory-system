<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            // Add foreign key to link returns to shifts
            $table->foreignId('sales_shift_id')->nullable()->constrained('sales_shifts')->nullOnDelete()->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropForeign(['sales_shift_id']);
            $table->dropColumn('sales_shift_id');
        });
    }
};

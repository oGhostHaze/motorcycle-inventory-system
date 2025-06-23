<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('price_histories', function (Blueprint $table) {
            $table->decimal('old_alt_price1', 10, 2)->nullable()->after('new_wholesale_price');
            $table->decimal('new_alt_price1', 10, 2)->nullable()->after('old_alt_price1');
            $table->decimal('old_alt_price2', 10, 2)->nullable()->after('new_alt_price1');
            $table->decimal('new_alt_price2', 10, 2)->nullable()->after('old_alt_price2');
            $table->decimal('old_alt_price3', 10, 2)->nullable()->after('new_alt_price2');
            $table->decimal('new_alt_price3', 10, 2)->nullable()->after('old_alt_price3');
        });
    }

    public function down()
    {
        Schema::table('price_histories', function (Blueprint $table) {
            $table->dropColumn([
                'old_alt_price1',
                'new_alt_price1',
                'old_alt_price2',
                'new_alt_price2',
                'old_alt_price3',
                'new_alt_price3'
            ]);
        });
    }
};

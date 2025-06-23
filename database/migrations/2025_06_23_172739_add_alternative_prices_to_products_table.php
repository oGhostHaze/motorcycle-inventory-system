<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('alt_price1', 10, 2)->nullable()->after('wholesale_price');
            $table->decimal('alt_price2', 10, 2)->nullable()->after('alt_price1');
            $table->decimal('alt_price3', 10, 2)->nullable()->after('alt_price2');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['alt_price1', 'alt_price2', 'alt_price3']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'dimensions', 'color', 'size', 'material']);
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('weight')->nullable();
            $table->text('dimensions')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('size', 50)->nullable();
            $table->string('material', 100)->nullable();
        });
    }
};

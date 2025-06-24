<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g., 'A1-B2', 'SHELF-001'
            $table->string('name'); // e.g., 'Aisle A, Shelf 1, Bin 2'
            $table->string('description')->nullable();
            $table->string('zone')->nullable(); // e.g., 'A', 'B', 'STORAGE'
            $table->string('section')->nullable(); // e.g., '1', '2', 'MAIN'
            $table->string('level')->nullable(); // e.g., 'TOP', 'MIDDLE', 'BOTTOM'
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['zone', 'section', 'level']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_locations');
    }
};

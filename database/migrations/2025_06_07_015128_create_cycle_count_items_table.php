<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cycle_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_count_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('system_quantity'); // What system says we have
            $table->integer('counted_quantity')->nullable(); // What was actually counted
            $table->integer('variance')->nullable(); // Difference
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('variance_value', 10, 2)->nullable(); // Financial impact
            $table->text('notes')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();

            $table->unique(['cycle_count_id', 'product_id', 'product_variant_id'], 'cycle_count_item_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cycle_count_items');
    }
};

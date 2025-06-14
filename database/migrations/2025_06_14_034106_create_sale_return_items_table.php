<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->string('reason'); // Specific reason for this item
            $table->enum('condition', ['good', 'damaged', 'defective'])->default('good');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['sale_return_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_return_items');
    }
};

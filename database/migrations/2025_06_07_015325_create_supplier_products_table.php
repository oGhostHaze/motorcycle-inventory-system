<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('supplier_sku')->nullable();
            $table->string('supplier_part_number')->nullable();
            $table->decimal('supplier_price', 10, 2);
            $table->integer('minimum_order_quantity')->default(1);
            $table->integer('lead_time_days')->default(7);
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('last_ordered_at')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'product_id']);
            $table->index(['product_id', 'is_preferred']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_products');
    }
};

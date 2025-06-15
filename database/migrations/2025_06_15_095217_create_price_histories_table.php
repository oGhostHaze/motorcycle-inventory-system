<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('old_cost_price', 10, 2)->nullable();
            $table->decimal('new_cost_price', 10, 2)->nullable();
            $table->decimal('old_selling_price', 10, 2)->nullable();
            $table->decimal('new_selling_price', 10, 2)->nullable();
            $table->decimal('old_wholesale_price', 10, 2)->nullable();
            $table->decimal('new_wholesale_price', 10, 2)->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};

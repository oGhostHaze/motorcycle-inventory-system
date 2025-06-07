<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('barcode_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('barcode');
            $table->enum('scan_type', ['lookup', 'sale', 'inventory', 'receiving'])->default('lookup');
            $table->string('device_type')->nullable(); // mobile, scanner, manual
            $table->boolean('was_successful')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['product_id', 'scan_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('barcode_scans');
    }
};

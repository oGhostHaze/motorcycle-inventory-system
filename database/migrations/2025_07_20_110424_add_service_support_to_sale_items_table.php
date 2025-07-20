<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Add service support columns
            $table->foreignId('service_id')->nullable()->after('product_id')->constrained('product_services')->nullOnDelete();
            $table->enum('item_type', ['product', 'service'])->default('product')->after('service_id');

            // Make product_id nullable since we can have services
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id', 'item_type']);

            // Note: Reverting product_id to NOT NULL might fail if there are service records
            // You may need to clean up service records first
            // $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};

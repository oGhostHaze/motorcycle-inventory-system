<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('inventory_location_id')->nullable()->after('location')->constrained('inventory_locations')->onDelete('set null');
            // Rename existing location field for migration purposes
            $table->renameColumn('location', 'location_legacy');
        });
    }

    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['inventory_location_id']);
            $table->dropColumn('inventory_location_id');
            $table->renameColumn('location_legacy', 'location');
        });
    }
};

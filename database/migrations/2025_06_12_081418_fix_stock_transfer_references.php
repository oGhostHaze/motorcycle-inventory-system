<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update any existing problematic reference_type values
        DB::table('stock_movements')
            ->where('reference_type', 'stock_transfer')
            ->update([
                'reference_type' => 'App\\Models\\StockTransfer',
            ]);

        // Alternatively, if you want to clear the problematic references:
        // DB::table('stock_movements')
        //     ->where('reference_type', 'stock_transfer')
        //     ->update([
        //         'reference_type' => null,
        //         'reference_id' => null,
        //     ]);
    }

    public function down()
    {
        // Revert back to the old format if needed
        DB::table('stock_movements')
            ->where('reference_type', 'App\\Models\\StockTransfer')
            ->update([
                'reference_type' => 'stock_transfer',
            ]);
    }
};

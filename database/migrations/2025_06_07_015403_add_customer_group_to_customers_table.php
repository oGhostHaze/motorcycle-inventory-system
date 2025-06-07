<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('customer_group_id')->nullable()->constrained()->onDelete('set null')->after('type');
            $table->date('date_of_birth')->nullable()->after('customer_group_id');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->decimal('total_purchases', 12, 2)->default(0)->after('credit_limit');
            $table->integer('total_orders')->default(0)->after('total_purchases');
            $table->timestamp('last_purchase_at')->nullable()->after('total_orders');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['customer_group_id']);
            $table->dropColumn([
                'customer_group_id',
                'date_of_birth',
                'gender',
                'total_purchases',
                'total_orders',
                'last_purchase_at'
            ]);
        });
    }
};

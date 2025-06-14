<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Who created the return
            $table->enum('type', ['refund', 'exchange', 'store_credit'])->default('refund');
            $table->string('reason'); // defective, wrong_item, not_as_described, etc.
            $table->text('notes')->nullable();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->enum('restock_condition', ['good', 'damaged', 'defective'])->default('good');
            $table->enum('status', ['pending', 'approved', 'processed', 'rejected'])->default('pending');

            // Approval tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Rejection tracking
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();

            // Processing tracking
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('return_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_returns');
    }
};

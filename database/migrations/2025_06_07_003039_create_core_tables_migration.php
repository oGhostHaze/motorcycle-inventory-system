<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Motorcycle Brands
        Schema::create('motorcycle_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Motorcycle Models
        Schema::create('motorcycle_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('motorcycle_brands')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('engine_type')->nullable(); // 4T, 2T, etc.
            $table->integer('engine_cc')->nullable();
            $table->year('year_from')->nullable();
            $table->year('year_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['brand_id', 'slug']);
        });

        // Product Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Product Subcategories
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_id', 'slug']);
        });

        // Product Brands
        Schema::create('product_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Suppliers/Vendors
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Philippines');
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('lead_time_days')->default(7);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Warehouses/Stores
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 10)->unique();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('phone')->nullable();
            $table->enum('type', ['main', 'branch', 'warehouse'])->default('main');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Products/Items
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('subcategory_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_brand_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('rfid_tag')->nullable()->unique();
            $table->text('description')->nullable();
            $table->text('specifications')->nullable(); // JSON field for flexible specs
            $table->string('part_number')->nullable();
            $table->string('oem_number')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->decimal('weight', 8, 3)->nullable(); // in kg
            $table->string('dimensions')->nullable(); // LxWxH
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('material')->nullable();
            $table->integer('warranty_months')->default(0);
            $table->boolean('track_serial')->default(false);
            $table->boolean('track_warranty')->default(false);
            $table->integer('min_stock_level')->default(0);
            $table->integer('max_stock_level')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->integer('reorder_quantity')->default(0);
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->json('images')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['product_brand_id', 'status']);
            $table->index('min_stock_level');
        });

        // Product Compatibility (Many-to-Many with motorcycle models)
        Schema::create('product_compatibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('motorcycle_model_id')->constrained()->onDelete('cascade');
            $table->year('year_from')->nullable();
            $table->year('year_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'motorcycle_model_id']);
        });

        // Product Variants (for size, color variations)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Red, Blue, 14inch, etc.
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->decimal('price_adjustment', 10, 2)->default(0);
            $table->json('attributes'); // {color: 'red', size: '14inch'}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Inventory/Stock
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_available')->storedAs('quantity_on_hand - quantity_reserved');
            $table->decimal('average_cost', 10, 2)->default(0);
            $table->string('location')->nullable(); // Shelf/Bin location
            $table->date('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id', 'product_variant_id']);
            $table->index(['warehouse_id', 'quantity_on_hand']);
        });

        // Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->enum('type', ['individual', 'business'])->default('individual');
            $table->string('tax_id')->nullable();
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Serial Numbers (for tracked items)
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('serial_number')->unique();
            $table->enum('status', ['in_stock', 'sold', 'warranty_return', 'defective'])->default('in_stock');
            $table->date('manufactured_date')->nullable();
            $table->date('warranty_expires_at')->nullable();
            $table->foreignId('sold_to_customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->timestamp('sold_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Sales/Transactions
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict'); // Cashier
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'gcash', 'paymaya'])->default('cash');
            $table->enum('status', ['draft', 'completed', 'cancelled', 'refunded'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        // Sale Items
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('restrict');
            $table->string('product_name'); // Store at time of sale
            $table->string('product_sku');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->json('serial_numbers')->nullable(); // For tracked items
            $table->timestamps();
        });

        // Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['draft', 'sent', 'confirmed', 'partial', 'completed', 'cancelled'])->default('draft');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->date('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('restrict');
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();
        });

        // Stock Movements/Audit Logs
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['sale', 'purchase', 'adjustment', 'transfer', 'return', 'damaged', 'cycle_count']);
            $table->integer('quantity_before');
            $table->integer('quantity_changed');
            $table->integer('quantity_after');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->foreignId('reference_id')->nullable(); // sale_id, purchase_order_id, etc.
            $table->string('reference_type')->nullable(); // Sale, PurchaseOrder, etc.
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        // Low Stock Alerts
        Schema::create('low_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->integer('current_stock');
            $table->integer('min_stock_level');
            $table->enum('status', ['active', 'resolved'])->default('active');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id', 'status']);
        });

        // Auto-Generated Purchase Orders (from low stock)
        Schema::create('auto_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'generated', 'cancelled'])->default('pending');
            $table->date('suggested_date');
            $table->json('products'); // Array of products and quantities
            $table->decimal('estimated_total', 10, 2);
            $table->foreignId('generated_po_id')->nullable()->constrained('purchase_orders')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auto_purchase_orders');
        Schema::dropIfExists('low_stock_alerts');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('serial_numbers');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_compatibility');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('product_brands');
        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('motorcycle_models');
        Schema::dropIfExists('motorcycle_brands');
    }
};

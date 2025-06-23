<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations with rollback on error
     */
    public function up(): void
    {

        try {
            DB::beginTransaction();
            // SALES TABLE INDEXES
            Schema::table('sales', function (Blueprint $table) {
                // For date range queries in all reports
                if (!$this->indexExists('sales', 'sales_completed_at_index')) {
                    $table->index('completed_at', 'sales_completed_at_index');
                }

                // For customer analysis with date filtering
                if (!$this->indexExists('sales', 'sales_customer_status_completed_index')) {
                    $table->index(['customer_id', 'status', 'completed_at'], 'sales_customer_status_completed_index');
                }

                // For user performance reports
                if (!$this->indexExists('sales', 'sales_user_status_completed_index')) {
                    $table->index(['user_id', 'status', 'completed_at'], 'sales_user_status_completed_index');
                }

                // For payment method analysis
                if (!$this->indexExists('sales', 'sales_payment_status_index')) {
                    $table->index(['payment_method', 'status'], 'sales_payment_status_index');
                }
            });

            // SALE_ITEMS TABLE INDEXES
            Schema::table('sale_items', function (Blueprint $table) {
                // For product performance analysis
                if (!$this->indexExists('sale_items', 'sale_items_product_sale_index')) {
                    $table->index(['product_id', 'sale_id'], 'sale_items_product_sale_index');
                }

                // For profit calculations (if cost_price is frequently queried)
                if (!$this->indexExists('sale_items', 'sale_items_cost_price_index')) {
                    $table->index('cost_price', 'sale_items_cost_price_index');
                }
            });

            // PRODUCTS TABLE INDEXES
            Schema::table('products', function (Blueprint $table) {
                // For name-based sorting in reports
                if (!$this->indexExists('products', 'products_name_index')) {
                    $table->index('name', 'products_name_index');
                }

                // For SKU lookups
                if (!$this->indexExists('products', 'products_sku_index')) {
                    $table->index('sku', 'products_sku_index');
                }
            });

            // INVENTORIES TABLE INDEXES
            Schema::table('inventories', function (Blueprint $table) {
                // Note: quantity_available is a generated column, indexing might not work on all MySQL versions
                // Only add if MySQL version supports indexing generated columns
                if ($this->supportsGeneratedColumnIndex() && !$this->indexExists('inventories', 'inventories_quantity_available_index')) {
                    $table->index('quantity_available', 'inventories_quantity_available_index');
                }

                // For low stock queries
                if (!$this->indexExists('inventories', 'inventories_product_quantity_index')) {
                    $table->index(['product_id', 'quantity_on_hand'], 'inventories_product_quantity_index');
                }
            });

            // STOCK_MOVEMENTS TABLE INDEXES
            Schema::table('stock_movements', function (Blueprint $table) {
                // For warehouse filtering
                if (!$this->indexExists('stock_movements', 'stock_movements_warehouse_id_index')) {
                    $table->index('warehouse_id', 'stock_movements_warehouse_id_index');
                }

                // For user filtering
                if (!$this->indexExists('stock_movements', 'stock_movements_user_id_index')) {
                    $table->index('user_id', 'stock_movements_user_id_index');
                }
            });

            // PURCHASE_ORDERS TABLE INDEXES
            Schema::table('purchase_orders', function (Blueprint $table) {
                // For date range queries in financial reports
                if (!$this->indexExists('purchase_orders', 'purchase_orders_order_date_index')) {
                    $table->index('order_date', 'purchase_orders_order_date_index');
                }

                // For warehouse and date filtering
                if (!$this->indexExists('purchase_orders', 'purchase_orders_warehouse_date_index')) {
                    $table->index(['warehouse_id', 'order_date'], 'purchase_orders_warehouse_date_index');
                }

                // For status and date filtering
                if (!$this->indexExists('purchase_orders', 'purchase_orders_status_date_index')) {
                    $table->index(['status', 'order_date'], 'purchase_orders_status_date_index');
                }
            });

            // CUSTOMERS TABLE INDEXES
            Schema::table('customers', function (Blueprint $table) {
                // For new customer analysis
                if (!$this->indexExists('customers', 'customers_created_at_index')) {
                    $table->index('created_at', 'customers_created_at_index');
                }

                // For active customer filtering
                if (!$this->indexExists('customers', 'customers_is_active_index')) {
                    $table->index('is_active', 'customers_is_active_index');
                }

                // For customer type filtering
                if (!$this->indexExists('customers', 'customers_type_index')) {
                    $table->index('type', 'customers_type_index');
                }
            });

            // CATEGORIES TABLE INDEXES
            Schema::table('categories', function (Blueprint $table) {
                // For name sorting
                if (!$this->indexExists('categories', 'categories_name_index')) {
                    $table->index('name', 'categories_name_index');
                }

                // For active filtering
                if (!$this->indexExists('categories', 'categories_is_active_index')) {
                    $table->index('is_active', 'categories_is_active_index');
                }
            });

            // PRODUCT_BRANDS TABLE INDEXES
            Schema::table('product_brands', function (Blueprint $table) {
                // For active filtering
                if (!$this->indexExists('product_brands', 'product_brands_is_active_index')) {
                    $table->index('is_active', 'product_brands_is_active_index');
                }
            });

            // WAREHOUSES TABLE INDEXES
            Schema::table('warehouses', function (Blueprint $table) {
                // For active filtering
                if (!$this->indexExists('warehouses', 'warehouses_is_active_index')) {
                    $table->index('is_active', 'warehouses_is_active_index');
                }
            });

            // SALE_RETURNS TABLE INDEXES (for shift returns tracking)
            Schema::table('sale_returns', function (Blueprint $table) {
                // For shift performance tracking
                if (!$this->indexExists('sale_returns', 'sale_returns_shift_status_index')) {
                    $table->index(['sales_shift_id', 'status'], 'sale_returns_shift_status_index');
                }
            });

            DB::commit();
            Log::info('Performance indexes added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add performance indexes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {

        try {
            DB::beginTransaction();
            // Drop indexes in reverse order
            Schema::table('sale_returns', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'sale_returns', 'sale_returns_shift_status_index');
            });

            Schema::table('warehouses', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'warehouses', 'warehouses_is_active_index');
            });

            Schema::table('product_brands', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'product_brands', 'product_brands_is_active_index');
            });

            Schema::table('categories', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'categories', 'categories_name_index');
                $this->dropIndexIfExists($table, 'categories', 'categories_is_active_index');
            });

            Schema::table('customers', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'customers', 'customers_created_at_index');
                $this->dropIndexIfExists($table, 'customers', 'customers_is_active_index');
                $this->dropIndexIfExists($table, 'customers', 'customers_type_index');
            });

            Schema::table('purchase_orders', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'purchase_orders', 'purchase_orders_order_date_index');
                $this->dropIndexIfExists($table, 'purchase_orders', 'purchase_orders_warehouse_date_index');
                $this->dropIndexIfExists($table, 'purchase_orders', 'purchase_orders_status_date_index');
            });

            Schema::table('stock_movements', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'stock_movements', 'stock_movements_warehouse_id_index');
                $this->dropIndexIfExists($table, 'stock_movements', 'stock_movements_user_id_index');
            });

            Schema::table('inventories', function (Blueprint $table) {
                if ($this->supportsGeneratedColumnIndex()) {
                    $this->dropIndexIfExists($table, 'inventories', 'inventories_quantity_available_index');
                }
                $this->dropIndexIfExists($table, 'inventories', 'inventories_product_quantity_index');
            });

            Schema::table('products', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'products', 'products_name_index');
                $this->dropIndexIfExists($table, 'products', 'products_sku_index');
            });

            Schema::table('sale_items', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'sale_items', 'sale_items_product_sale_index');
                $this->dropIndexIfExists($table, 'sale_items', 'sale_items_cost_price_index');
            });

            Schema::table('sales', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'sales', 'sales_completed_at_index');
                $this->dropIndexIfExists($table, 'sales', 'sales_customer_status_completed_index');
                $this->dropIndexIfExists($table, 'sales', 'sales_user_status_completed_index');
                $this->dropIndexIfExists($table, 'sales', 'sales_payment_status_index');
            });

            DB::commit();
            Log::info('Performance indexes dropped successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to drop performance indexes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            Log::warning("Could not check index existence for {$table}.{$indexName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Drop an index if it exists
     */
    private function dropIndexIfExists(Blueprint $table, string $tableName, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            $table->dropIndex($indexName);
        }
    }

    /**
     * Check if MySQL version supports indexing generated columns
     */
    private function supportsGeneratedColumnIndex(): bool
    {
        try {
            $version = DB::select("SELECT VERSION() as version")[0]->version;
            // MySQL 5.7.8+ supports indexes on generated columns
            preg_match('/^(\d+)\.(\d+)\.(\d+)/', $version, $matches);
            if (count($matches) >= 4) {
                $major = (int)$matches[1];
                $minor = (int)$matches[2];
                $patch = (int)$matches[3];

                if ($major > 5) return true;
                if ($major == 5 && $minor > 7) return true;
                if ($major == 5 && $minor == 7 && $patch >= 8) return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::warning("Could not determine MySQL version: " . $e->getMessage());
            return false;
        }
    }
};

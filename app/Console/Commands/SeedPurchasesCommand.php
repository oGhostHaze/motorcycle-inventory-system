<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\PurchaseSeeder;

class SeedPurchasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:purchases
                            {--stock-only : Only add stock without creating purchase orders}
                            {--quantity= : Default quantity to add per product (default: 50)}
                            {--warehouse= : Specific warehouse ID to add stock to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed purchase orders and add stock quantities to inventory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting purchases seeding...');

        if ($this->option('stock-only')) {
            $this->addStockOnly();
        } else {
            $this->call('db:seed', ['--class' => PurchaseSeeder::class]);
        }

        $this->info('✅ Purchases seeding completed!');
    }

    /**
     * Add stock quantities only without creating purchase orders
     */
    private function addStockOnly()
    {
        $quantity = $this->option('quantity') ?? 50;
        $warehouseId = $this->option('warehouse');

        $this->info("📦 Adding {$quantity} units to each product...");

        // Get warehouses
        if ($warehouseId) {
            $warehouses = \App\Models\Warehouse::where('id', $warehouseId)->get();
        } else {
            $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        }

        if ($warehouses->isEmpty()) {
            $this->error('❌ No active warehouses found');
            return;
        }

        // Get products
        $products = \App\Models\Product::where('status', 'active')->get();

        if ($products->isEmpty()) {
            $this->error('❌ No active products found');
            return;
        }

        $this->info("📋 Processing {$products->count()} products across {$warehouses->count()} warehouse(s)...");

        $progressBar = $this->output->createProgressBar($products->count() * $warehouses->count());

        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                $this->addStockToProduct($product, $warehouse, $quantity);
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Added stock to {$products->count()} products in {$warehouses->count()} warehouse(s)");
    }

    /**
     * Add stock to a specific product in a warehouse
     */
    private function addStockToProduct($product, $warehouse, $quantity)
    {
        $unitCost = $product->cost_price ?: rand(100, 5000);

        // Update or create inventory
        $inventory = \App\Models\Inventory::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        if ($inventory) {
            // Calculate new average cost
            $currentValue = $inventory->quantity_on_hand * $inventory->average_cost;
            $newValue = $quantity * $unitCost;
            $totalQuantity = $inventory->quantity_on_hand + $quantity;
            $newAverageCost = $totalQuantity > 0 ? ($currentValue + $newValue) / $totalQuantity : $unitCost;

            $oldQuantity = $inventory->quantity_on_hand;
            $inventory->update([
                'quantity_on_hand' => $inventory->quantity_on_hand + $quantity,
                'quantity_available' => $inventory->quantity_available + $quantity,
                'average_cost' => $newAverageCost,
                'last_received_at' => now(),
            ]);
        } else {
            $oldQuantity = 0;
            \App\Models\Inventory::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity_on_hand' => $quantity,
                'quantity_available' => $quantity,
                'quantity_reserved' => 0,
                'average_cost' => $unitCost,
                'last_received_at' => now(),
            ]);
        }

        // Create stock movement
        \App\Models\StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'adjustment',
            'quantity_before' => $oldQuantity,
            'quantity_changed' => $quantity,
            'quantity_after' => $oldQuantity + $quantity,
            'unit_cost' => $unitCost,
            'reference_type' => 'manual_seeding',
            'reference_id' => null,
            'user_id' => 1, // Assuming admin user ID is 1
            'notes' => 'Stock added via seeding command',
        ]);
    }
}

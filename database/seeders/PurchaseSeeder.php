<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Inventory;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting purchase orders seeding...');

        // Get required data
        $suppliers = Supplier::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('status', 'active')->get();
        $users = User::all();

        if ($suppliers->isEmpty() || $warehouses->isEmpty() || $products->isEmpty()) {
            $this->command->error('❌ Missing required data: suppliers, warehouses, or products');
            return;
        }

        $this->command->info("📦 Found {$suppliers->count()} suppliers, {$warehouses->count()} warehouses, {$products->count()} products");

        DB::beginTransaction();

        try {
            // Create purchase orders with different scenarios
            $this->createCompletedPurchases($suppliers, $warehouses, $products, $users);
            $this->createPartialPurchases($suppliers, $warehouses, $products, $users);
            $this->createPendingPurchases($suppliers, $warehouses, $products, $users);
            $this->createDraftPurchases($suppliers, $warehouses, $products, $users);

            DB::commit();
            $this->command->info('✅ Purchase orders seeding completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error seeding purchases: ' . $e->getMessage());
        }
    }

    /**
     * Create completed purchase orders that add stock to inventory
     */
    private function createCompletedPurchases($suppliers, $warehouses, $products, $users)
    {
        $this->command->info('📋 Creating completed purchase orders...');

        for ($i = 1; $i <= 15; $i++) {
            $supplier = $suppliers->random();
            $warehouse = $warehouses->random();
            $user = $users->random();

            $orderDate = Carbon::now()->subDays(rand(30, 180));
            $expectedDate = $orderDate->copy()->addDays(rand(7, 21));
            $receivedDate = $expectedDate->copy()->addDays(rand(-3, 7));

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePONumber($orderDate),
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'requested_by' => $user->id,
                'status' => 'completed',
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'received_date' => $receivedDate,
                'notes' => 'Bulk inventory replenishment - Completed',
                'total_amount' => 0, // Will be calculated
            ]);

            $totalAmount = 0;
            $itemCount = rand(3, 8);
            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                $quantityOrdered = rand(10, 100);
                $quantityReceived = $quantityOrdered; // Fully received for completed orders
                $unitCost = $product->cost_price ?: rand(100, 5000);
                $totalCost = $quantityReceived * $unitCost;
                $totalAmount += $totalCost;

                // Create purchase order item
                $poItem = PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'quantity_ordered' => $quantityOrdered,
                    'quantity_received' => $quantityReceived,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                // Update or create inventory
                $this->updateInventory($product->id, $warehouse->id, $quantityReceived, $unitCost);

                // Create stock movement
                $this->createStockMovement([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'type' => 'purchase_receipt',
                    'quantity_before' => 0, // Will be calculated
                    'quantity_changed' => $quantityReceived,
                    'quantity_after' => 0, // Will be calculated
                    'unit_cost' => $unitCost,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $purchaseOrder->id,
                    'user_id' => $user->id,
                    'notes' => "Received from PO: {$purchaseOrder->po_number}",
                    'created_at' => $receivedDate,
                ]);
            }

            // Update total amount
            $purchaseOrder->update(['total_amount' => $totalAmount]);
        }

        $this->command->info('✅ Created 15 completed purchase orders');
    }

    /**
     * Create partial purchase orders
     */
    private function createPartialPurchases($suppliers, $warehouses, $products, $users)
    {
        $this->command->info('📋 Creating partial purchase orders...');

        for ($i = 1; $i <= 5; $i++) {
            $supplier = $suppliers->random();
            $warehouse = $warehouses->random();
            $user = $users->random();

            $orderDate = Carbon::now()->subDays(rand(7, 30));
            $expectedDate = $orderDate->copy()->addDays(rand(7, 14));

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePONumber($orderDate),
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'requested_by' => $user->id,
                'status' => 'partial',
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => 'Partial delivery - awaiting remaining items',
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            $itemCount = rand(2, 5);
            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                $quantityOrdered = rand(20, 80);
                $quantityReceived = rand(5, $quantityOrdered - 5); // Partial delivery
                $unitCost = $product->cost_price ?: rand(100, 5000);
                $totalCost = $quantityOrdered * $unitCost;
                $totalAmount += $totalCost;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'quantity_ordered' => $quantityOrdered,
                    'quantity_received' => $quantityReceived,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                if ($quantityReceived > 0) {
                    // Update inventory for received quantity
                    $this->updateInventory($product->id, $warehouse->id, $quantityReceived, $unitCost);

                    // Create stock movement
                    $this->createStockMovement([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'type' => 'purchase_receipt',
                        'quantity_before' => 0,
                        'quantity_changed' => $quantityReceived,
                        'quantity_after' => 0,
                        'unit_cost' => $unitCost,
                        'reference_type' => PurchaseOrder::class,
                        'reference_id' => $purchaseOrder->id,
                        'user_id' => $user->id,
                        'notes' => "Partial receipt from PO: {$purchaseOrder->po_number}",
                        'created_at' => $orderDate->copy()->addDays(rand(1, 5)),
                    ]);
                }
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);
        }

        $this->command->info('✅ Created 5 partial purchase orders');
    }

    /**
     * Create pending purchase orders
     */
    private function createPendingPurchases($suppliers, $warehouses, $products, $users)
    {
        $this->command->info('📋 Creating pending purchase orders...');

        for ($i = 1; $i <= 8; $i++) {
            $supplier = $suppliers->random();
            $warehouse = $warehouses->random();
            $user = $users->random();

            $orderDate = Carbon::now()->subDays(rand(1, 14));
            $expectedDate = $orderDate->copy()->addDays(rand(3, 10));

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePONumber($orderDate),
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'requested_by' => $user->id,
                'status' => 'pending',
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => 'Awaiting delivery from supplier',
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            $itemCount = rand(2, 6);
            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                $quantityOrdered = rand(15, 60);
                $unitCost = $product->cost_price ?: rand(100, 5000);
                $totalCost = $quantityOrdered * $unitCost;
                $totalAmount += $totalCost;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'quantity_ordered' => $quantityOrdered,
                    'quantity_received' => 0, // Not received yet
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);
        }

        $this->command->info('✅ Created 8 pending purchase orders');
    }

    /**
     * Create draft purchase orders
     */
    private function createDraftPurchases($suppliers, $warehouses, $products, $users)
    {
        $this->command->info('📋 Creating draft purchase orders...');

        for ($i = 1; $i <= 3; $i++) {
            $supplier = $suppliers->random();
            $warehouse = $warehouses->random();
            $user = $users->random();

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePONumber(Carbon::now()),
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'requested_by' => $user->id,
                'status' => 'draft',
                'order_date' => Carbon::now(),
                'expected_date' => Carbon::now()->addDays(rand(7, 14)),
                'notes' => 'Draft order - pending review',
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            $itemCount = rand(1, 3);
            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                $quantityOrdered = rand(10, 40);
                $unitCost = $product->cost_price ?: rand(100, 5000);
                $totalCost = $quantityOrdered * $unitCost;
                $totalAmount += $totalCost;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'quantity_ordered' => $quantityOrdered,
                    'quantity_received' => 0,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);
        }

        $this->command->info('✅ Created 3 draft purchase orders');
    }

    /**
     * Update or create inventory record
     */
    private function updateInventory($productId, $warehouseId, $quantity, $unitCost)
    {
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($inventory) {
            // Calculate new average cost
            $currentValue = $inventory->quantity_on_hand * $inventory->average_cost;
            $newValue = $quantity * $unitCost;
            $totalQuantity = $inventory->quantity_on_hand + $quantity;
            $newAverageCost = $totalQuantity > 0 ? ($currentValue + $newValue) / $totalQuantity : $unitCost;

            $inventory->update([
                'quantity_on_hand' => $inventory->quantity_on_hand + $quantity,
                'quantity_available' => $inventory->quantity_available + $quantity,
                'average_cost' => $newAverageCost,
                'last_received_at' => now(),
            ]);
        } else {
            Inventory::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity_on_hand' => $quantity,
                'quantity_available' => $quantity,
                'quantity_reserved' => 0,
                'average_cost' => $unitCost,
                'last_received_at' => now(),
            ]);
        }
    }

    /**
     * Create stock movement record
     */
    private function createStockMovement($data)
    {
        // Get current inventory to calculate before/after quantities
        $inventory = Inventory::where('product_id', $data['product_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->first();

        if ($inventory) {
            $quantityAfter = $inventory->quantity_on_hand;
            $quantityBefore = $quantityAfter - $data['quantity_changed'];
        } else {
            $quantityBefore = 0;
            $quantityAfter = $data['quantity_changed'];
        }

        StockMovement::create([
            'product_id' => $data['product_id'],
            'warehouse_id' => $data['warehouse_id'],
            'type' => $data['type'],
            'quantity_before' => $quantityBefore,
            'quantity_changed' => $data['quantity_changed'],
            'quantity_after' => $quantityAfter,
            'unit_cost' => $data['unit_cost'],
            'reference_type' => $data['reference_type'],
            'reference_id' => $data['reference_id'],
            'user_id' => $data['user_id'],
            'notes' => $data['notes'],
            'created_at' => $data['created_at'] ?? now(),
        ]);
    }

    /**
     * Generate purchase order number
     */
    private function generatePONumber($date)
    {
        $sequence = PurchaseOrder::whereDate('created_at', $date->format('Y-m-d'))->count() + 1;
        return 'PO-' . $date->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

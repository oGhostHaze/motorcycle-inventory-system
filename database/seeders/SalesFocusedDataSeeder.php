<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SalesShift;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\SerialNumber;
use App\Models\WarrantyClaim;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesFocusedDataSeeder extends Seeder
{
    private $warehouses = [];
    private $users = [];
    private $customers = [];
    private $products = [];
    private $customerGroups = [];
    private $salesCounter = [];
    private $globalShiftCounter = 1; // Global shift counter for uniqueness

    // Sales patterns and probabilities
    private $paymentMethods = ['cash', 'card', 'bank_transfer'];

    // Seasonal sales multipliers (by month)
    private $seasonalMultipliers = [
        1 => 0.8,  // January - post-holiday slowdown
        2 => 0.9,  // February
        3 => 1.0,  // March
        4 => 1.1,  // April - riding season starts
        5 => 1.2,  // May - peak riding
        6 => 1.3,  // June - peak season
        7 => 1.3,  // July - peak season
        8 => 1.2,  // August - still high
        9 => 1.1,  // September
        10 => 1.0, // October
        11 => 0.9, // November
        12 => 1.4, // December - Christmas season
    ];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('🚀 Starting Sales Focused Data Seeder (2024-2026)');

        // Clear all related tables first
        $this->clearAllTables();

        // Load existing data
        $this->loadExistingData();

        // Generate sales data for 2024-2026
        $this->generateHistoricalSalesData();

        // Generate some returns and warranty claims
        $this->generateReturnsAndWarranty();

        $this->command->info('✅ Sales data generation complete!');
        $this->printSummary();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function clearAllTables(): void
    {
        $this->command->info('🧹 Clearing all sales-related tables...');

        // Clear in proper order to avoid foreign key constraints
        try {
            // Clear warranty and review data
            WarrantyClaim::truncate();
            $this->command->info('  • Cleared warranty_claims');

            // Clear return data
            SaleReturnItem::truncate();
            $this->command->info('  • Cleared sale_return_items');

            SaleReturn::truncate();
            $this->command->info('  • Cleared sale_returns');

            // Clear sales data
            SaleItem::truncate();
            $this->command->info('  • Cleared sale_items');

            Sale::truncate();
            $this->command->info('  • Cleared sales');

            // Clear shift data
            SalesShift::truncate();
            $this->command->info('  • Cleared sales_shifts');

            // Clear stock movements (only sale types)
            $deletedMovements = StockMovement::where('type', 'sale')->delete();
            $this->command->info("  • Cleared {$deletedMovements} sale stock movements");

            // Clear sold serial numbers (keep unsold ones)
            $deletedSerials = SerialNumber::where('status', 'sold')->delete();
            $this->command->info("  • Cleared {$deletedSerials} sold serial numbers");

            // Clear customers and customer groups (we'll recreate them)
            Customer::truncate();
            $this->command->info('  • Cleared customers');

            CustomerGroup::truncate();
            $this->command->info('  • Cleared customer_groups');

            // Reset auto-increment counters
            DB::statement('ALTER TABLE sales AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE sale_items AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE sales_shifts AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE customers AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE customer_groups AUTO_INCREMENT = 1');

            $this->command->info('✅ All tables cleared successfully!');
        } catch (\Exception $e) {
            $this->command->error('❌ Error clearing tables: ' . $e->getMessage());
            throw $e;
        }
    }

    private function loadExistingData(): void
    {
        $this->command->info('📋 Loading existing data...');

        // Load warehouses
        $this->warehouses = Warehouse::where('is_active', true)->get()->toArray();
        if (empty($this->warehouses)) {
            $this->command->error('❌ No active warehouses found! Please seed warehouses first.');
            return;
        }

        // Load users
        $this->users = User::where('is_active', true)->get()->keyBy('id')->toArray();
        if (empty($this->users)) {
            $this->command->error('❌ No active users found! Please seed users first.');
            return;
        }

        // Load products with inventory
        $this->products = Product::where('status', 'active')->get()->toArray();

        if (empty($this->products)) {
            $this->command->error('❌ No active products found! Please seed products first.');
            return;
        }

        // Load existing customers
        $this->customers = Customer::where('is_active', true)->get()->toArray();
        if (empty($this->customers)) {
            $this->command->warn('⚠️  No active customers found! Creating minimal customers...');
            $this->createMinimalCustomers();
            $this->customers = Customer::where('is_active', true)->get()->toArray();
        }

        // Load existing customer groups
        $this->customerGroups = CustomerGroup::where('is_active', true)->get()->toArray();
        if (empty($this->customerGroups)) {
            $this->command->warn('⚠️  No active customer groups found! Creating minimal customer groups...');
            $this->createMinimalCustomerGroups();
            $this->customerGroups = CustomerGroup::where('is_active', true)->get()->toArray();
        }

        $this->command->info("✅ Loaded/Created: " . count($this->warehouses) . " warehouses, " .
            count($this->users) . " users, " .
            count($this->products) . " products, " .
            count($this->customers) . " customers, " .
            count($this->customerGroups) . " customer groups");
    }

    private function generateHistoricalSalesData(): void
    {
        $this->command->info('📊 Generating historical sales data...');

        $startDate = Carbon::create(2024, 1, 1);
        $endDate = Carbon::create(2026, 12, 31);
        $currentDate = $startDate->copy();
        $dayCounter = 0;
        $totalSales = 0;

        while ($currentDate <= $endDate) {
            $dayCounter++;

            if ($dayCounter % 100 == 0) {
                $this->command->info("📅 Processing day {$dayCounter}: {$currentDate->format('Y-m-d')} (Total sales: {$totalSales})");
            }

            $salesCount = $this->generateDailyData($currentDate);
            $totalSales += $salesCount;

            $currentDate->addDay();
        }

        $this->command->info("✅ Generated {$totalSales} sales over {$dayCounter} days");
    }

    private function generateDailyData(Carbon $date): int
    {
        $salesGenerated = 0;

        // Skip some days (closed days)
        if ($this->isStoreClosed($date)) {
            return 0;
        }

        // Determine number of shifts (1-2 shifts per day)
        $shiftsToday = $this->isWeekend($date) ? 1 : (rand(1, 100) <= 70 ? 2 : 1);

        for ($shiftNum = 1; $shiftNum <= $shiftsToday; $shiftNum++) {
            $shift = $this->createSalesShift($date, $shiftNum);
            $shiftSales = $this->generateShiftSales($shift, $date);
            $this->closeSalesShift($shift);
            $salesGenerated += $shiftSales;
        }

        return $salesGenerated;
    }

    private function isStoreClosed(Carbon $date): bool
    {
        // Closed on some holidays and random days (5% chance)
        $holidays = [
            '01-01', // New Year
            '12-25', // Christmas
            '12-30', // Rizal Day
        ];

        $dateString = $date->format('m-d');
        return in_array($dateString, $holidays) || rand(1, 100) <= 5;
    }

    private function isWeekend(Carbon $date): bool
    {
        return $date->isWeekend();
    }

    private function createSalesShift(Carbon $date, int $shiftNumber): SalesShift
    {
        // Generate truly unique shift number using global counter
        $uniqueShiftNumber = 'SH-' . $date->format('Ymd') . '-' . str_pad($this->globalShiftCounter, 4, '0', STR_PAD_LEFT);
        $this->globalShiftCounter++;

        // Assign random cashier
        $cashiers = array_filter($this->users, fn($user) => $user['role'] === 'cashier');
        if (empty($cashiers)) {
            // Fallback to any user if no cashiers found
            $assignedUser = $this->users[array_rand($this->users)];
        } else {
            $assignedUser = $cashiers[array_rand($cashiers)];
        }

        // Shift times
        if ($shiftNumber === 1) {
            $startTime = $date->copy()->setTime(8, 0, 0);
        } else {
            $startTime = $date->copy()->setTime(16, 0, 0);
        }

        return SalesShift::create([
            'shift_number' => $uniqueShiftNumber,
            'user_id' => $assignedUser['id'],
            'warehouse_id' => $this->warehouses[array_rand($this->warehouses)]['id'],
            'started_at' => $startTime,
            'ended_at' => null, // Will be set when closed
            'opening_cash' => rand(5000, 20000),
            'closing_cash' => 0, // Will be calculated
            'expected_cash' => 0, // Will be calculated
            'cash_difference' => 0, // Will be calculated
            'total_sales' => 0,
            'total_transactions' => 0,
            'cash_sales' => 0,
            'card_sales' => 0,
            'other_sales' => 0,
            'opening_notes' => 'Shift started normally',
            'closing_notes' => '', // Will be set when closed
            'status' => 'active',
            'total_returns_count' => 0,
            'total_returns_amount' => 0,
            'processed_returns_count' => 0,
            'processed_returns_amount' => 0,
        ]);
    }

    private function generateShiftSales(SalesShift $shift, Carbon $date): int
    {
        // Determine base number of sales for this shift
        $month = $date->month;
        $seasonalMultiplier = $this->seasonalMultipliers[$month];
        $weekdayMultiplier = $this->isWeekend($date) ? 1.2 : 1.0; // Weekends are busier

        $baseSalesCount = rand(8, 25); // Base sales per shift
        $adjustedSalesCount = (int) ($baseSalesCount * $seasonalMultiplier * $weekdayMultiplier);

        $salesGenerated = 0;

        for ($i = 0; $i < $adjustedSalesCount; $i++) {
            $this->generateSingleSale($shift, $date);
            $salesGenerated++;
        }

        return $salesGenerated;
    }

    private function generateSingleSale(SalesShift $shift, Carbon $date): void
    {
        // Random sale time within shift hours
        $shiftStart = $shift->started_at;
        $shiftDuration = 8 * 60; // 8 hours in minutes
        $randomMinutes = rand(0, $shiftDuration - 60);
        $saleTime = $shiftStart->copy()->addMinutes($randomMinutes);

        // 70% chance of having a customer, 30% walk-in
        $customer = null;
        if (rand(1, 100) <= 70) {
            $customer = $this->customers[array_rand($this->customers)];
        }

        // Payment method selection
        $paymentMethod = $this->selectPaymentMethod();

        // Create sale
        $sale = Sale::create([
            'invoice_number' => $this->generateInvoiceNumber($date),
            'customer_id' => $customer ? $customer['id'] : null,
            'warehouse_id' => $shift->warehouse_id,
            'user_id' => $shift->user_id,
            'shift_id' => $shift->id,
            'subtotal' => 0, // Will be calculated
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => $paymentMethod,
            'status' => 'completed',
            'notes' => '',
            'completed_at' => $saleTime,
            'created_at' => $saleTime,
            'updated_at' => $saleTime,
        ]);

        // Generate sale items (1-5 items per sale)
        $itemCount = rand(1, 5);
        $subtotal = 0;

        for ($j = 0; $j < $itemCount; $j++) {
            $product = $this->products[array_rand($this->products)];
            $quantity = rand(1, 3);

            // Apply customer group discount if applicable
            $unitPrice = $product['selling_price'];
            $discount = 0;

            if ($customer && isset($customer['customer_group_id']) && $customer['customer_group_id']) {
                $customerGroup = collect($this->customerGroups)->firstWhere('id', $customer['customer_group_id']);
                if ($customerGroup && isset($customerGroup['discount_percentage'])) {
                    $discount = ($unitPrice * $customerGroup['discount_percentage']) / 100;
                    $unitPrice -= $discount;
                }
            }

            $totalPrice = $unitPrice * $quantity;
            $subtotal += $totalPrice;

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product['id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'cost_price' => $product['cost_price'],
                'discount_amount' => $discount * $quantity,
            ]);

            // Update inventory (decrease stock)
            $this->updateInventory($product['id'], $shift->warehouse_id, -$quantity, 'sale', $sale->id);
        }

        // Calculate totals
        $taxAmount = $subtotal * 0.12; // 12% VAT
        $totalAmount = $subtotal + $taxAmount;
        $paidAmount = $totalAmount;

        // Update sale totals
        $sale->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'change_amount' => 0,
        ]);

        // Update shift totals
        $this->updateShiftTotals($shift, $totalAmount, $paymentMethod);

        // Update customer totals if applicable
        if ($customer) {
            $this->updateCustomerTotals($customer['id'], $totalAmount);
        }
    }

    private function selectPaymentMethod(): string
    {
        $rand = rand(1, 100);

        if ($rand <= 50) return 'cash';
        if ($rand <= 90) return 'card';
        return 'bank_transfer';
    }

    private function generateInvoiceNumber(Carbon $date): string
    {
        $dateKey = $date->format('Ymd');

        if (!isset($this->salesCounter[$dateKey])) {
            $this->salesCounter[$dateKey] = 0;
        }

        $this->salesCounter[$dateKey]++;

        return 'INV-' . $dateKey . '-' . str_pad($this->salesCounter[$dateKey], 4, '0', STR_PAD_LEFT);
    }

    private function updateInventory(int $productId, int $warehouseId, int $quantityChange, string $type, int $referenceId): void
    {
        // Find or create inventory record
        $inventory = Inventory::firstOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity_on_hand' => 1000, // Start with good stock
                'quantity_reserved' => 0,
                'average_cost' => 0,
                'location' => 'A1-B1',
                'last_counted_at' => now(),
            ]
        );

        // Get quantities for stock movement
        $quantityBefore = $inventory->quantity_on_hand;
        $quantityAfter = $quantityBefore + $quantityChange;

        // Update inventory
        $inventory->update(['quantity_on_hand' => $quantityAfter]);

        // Create stock movement record with correct field names
        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'product_variant_id' => null, // No variants in this seeder
            'type' => $type,
            'quantity_before' => $quantityBefore,
            'quantity_changed' => abs($quantityChange),
            'quantity_after' => $quantityAfter,
            'unit_cost' => 0, // Will be updated if needed
            'reference_id' => $referenceId,
            'reference_type' => Sale::class,
            'user_id' => 1, // Default to admin
            'notes' => ucfirst($type) . ' transaction',
        ]);
    }

    private function updateShiftTotals(SalesShift $shift, float $amount, string $paymentMethod): void
    {
        $shift->increment('total_sales', $amount);
        $shift->increment('total_transactions', 1);

        switch ($paymentMethod) {
            case 'cash':
                $shift->increment('cash_sales', $amount);
                break;
            case 'card':
                $shift->increment('card_sales', $amount);
                break;
            default:
                // All other payment methods go to other_sales
                $shift->increment('other_sales', $amount);
                break;
        }
    }

    private function updateCustomerTotals(int $customerId, float $amount): void
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $customer->increment('total_purchases', $amount);
            $customer->increment('total_orders', 1);
            $customer->update(['last_purchase_at' => now()]);
        }
    }

    private function closeSalesShift(SalesShift $shift): void
    {
        // Calculate expected cash
        $expectedCash = $shift->opening_cash + $shift->cash_sales;
        $actualClosingCash = $expectedCash + rand(-500, 500); // Small variance for realism

        $shift->update([
            'ended_at' => $shift->started_at->copy()->addHours(8),
            'closing_cash' => $actualClosingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $actualClosingCash - $expectedCash,
            'closing_notes' => 'Shift completed normally',
            'status' => 'completed',
        ]);
    }

    private function generateReturnsAndWarranty(): void
    {
        $this->command->info('🔄 Generating returns and warranty claims...');

        // Generate some returns (2% of total sales)
        $totalSales = Sale::count();
        $returnsToGenerate = (int) ($totalSales * 0.02);

        $sales = Sale::with('items.product')->inRandomOrder()->limit($returnsToGenerate)->get();

        foreach ($sales as $sale) {
            $this->generateSaleReturn($sale);
        }

        // Generate warranty claims (1% of total sales)
        $warrantyToGenerate = (int) ($totalSales * 0.01);
        $warrantySales = Sale::with('items.product', 'customer')
            ->whereNotNull('customer_id')
            ->inRandomOrder()
            ->limit($warrantyToGenerate)
            ->get();

        foreach ($warrantySales as $sale) {
            $this->generateWarrantyClaim($sale);
        }

        $this->command->info("✅ Generated {$returnsToGenerate} returns and {$warrantyToGenerate} warranty claims");
    }

    private function createMinimalCustomerGroups(): void
    {
        $this->command->info('👥 Creating minimal customer groups...');

        $groups = [
            [
                'name' => 'Regular Customers',
                'slug' => 'regular-customers',
                'description' => 'Standard customer group',
                'discount_percentage' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'VIP Customers',
                'slug' => 'vip-customers',
                'description' => 'High-value customers',
                'discount_percentage' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($groups as $groupData) {
            CustomerGroup::create($groupData);
        }
    }

    private function createMinimalCustomers(): void
    {
        $this->command->info('👤 Creating minimal customers...');

        // Ensure we have customer groups first
        if (CustomerGroup::count() == 0) {
            $this->createMinimalCustomerGroups();
        }

        $regularGroup = CustomerGroup::where('name', 'Regular Customers')->first();

        // Simple customer names
        $names = [
            'Juan Dela Cruz',
            'Maria Santos',
            'Jose Reyes',
            'Ana Garcia',
            'Carlos Mendoza',
            'Rosa Torres',
            'Miguel Bautista',
            'Elena Ocampo',
            'Ricardo Aguilar',
            'Carmen Lopez',
            'Antonio Flores',
            'Isabel Ramos',
            'Francisco Diaz',
            'Teresa Fernandez',
            'Manuel Perez',
            'Patricia Soriano',
            'Rafael Castillo',
            'Monica Morales',
            'Gabriel Aquino',
            'Sofia Lim'
        ];

        foreach ($names as $index => $name) {
            Customer::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@email.com',
                'phone' => '09' . rand(100000000, 999999999),
                'address' => (100 + $index) . ' Sample Street',
                'city' => 'Manila',
                'type' => 'individual',
                'customer_group_id' => $regularGroup->id,
                'date_of_birth' => Carbon::now()->subYears(rand(25, 55)),
                'gender' => $index % 2 == 0 ? 'male' : 'female',
                'tax_id' => null,
                'credit_limit' => 50000,
                'total_purchases' => 0,
                'total_orders' => 0,
                'last_purchase_at' => null,
                'notes' => 'Auto-created customer',
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ Created 20 minimal customers');
    }

    private function generateSaleReturn(Sale $sale): void
    {
        $returnDate = $sale->created_at->copy()->addDays(rand(1, 30));

        $saleReturn = SaleReturn::create([
            'return_number' => 'RET-' . $returnDate->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'user_id' => $sale->user_id,
            'type' => ['refund', 'exchange', 'store_credit'][array_rand(['refund', 'exchange', 'store_credit'])],
            'reason' => ['defective', 'wrong_item', 'not_as_described', 'customer_changed_mind'][array_rand(['defective', 'wrong_item', 'not_as_described', 'customer_changed_mind'])],
            'notes' => 'Customer return request',
            'refund_amount' => 0, // Will be calculated
            'restock_condition' => ['good', 'damaged', 'defective'][array_rand(['good', 'damaged', 'defective'])],
            'status' => 'processed',
            'approved_by' => array_values(array_filter($this->users, fn($user) => $user['role'] === 'manager'))[0]['id'] ?? 1,
            'approved_at' => $returnDate,
            'processed_by' => $sale->user_id,
            'processed_at' => $returnDate->copy()->addHours(1),
            'created_at' => $returnDate,
            'updated_at' => $returnDate,
        ]);

        // Return some items (usually 1-2 items)
        $itemsToReturn = $sale->items->random(min($sale->items->count(), rand(1, 2)));
        $totalRefund = 0;

        foreach ($itemsToReturn as $saleItem) {
            $returnQuantity = min($saleItem->quantity, rand(1, $saleItem->quantity));
            $returnAmount = ($saleItem->unit_price * $returnQuantity);
            $totalRefund += $returnAmount;

            SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'sale_item_id' => $saleItem->id,
                'product_id' => $saleItem->product_id,
                'quantity' => $returnQuantity,
                'unit_price' => $saleItem->unit_price,
                'total_price' => $returnAmount,
                'reason' => $saleReturn->reason,
                'condition' => $saleReturn->restock_condition,
                'notes' => 'Returned item',
            ]);
        }

        $saleReturn->update(['refund_amount' => $totalRefund]);
    }

    private function generateWarrantyClaim(Sale $sale): void
    {
        $claimDate = $sale->created_at->copy()->addDays(rand(30, 365));
        $saleItem = $sale->items->random();

        WarrantyClaim::create([
            'claim_number' => 'WC-' . $claimDate->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_id' => $sale->customer_id,
            'product_id' => $saleItem->product_id,
            'sale_id' => $sale->id,
            'purchase_date' => $sale->created_at->toDateString(),
            'claim_date' => $claimDate->toDateString(),
            'issue_description' => 'Product malfunction reported by customer',
            'status' => ['pending', 'approved', 'rejected', 'resolved'][array_rand(['pending', 'approved', 'rejected', 'resolved'])],
            'resolution_type' => ['repair', 'replace', 'refund'][array_rand(['repair', 'replace', 'refund'])],
            'claim_amount' => $saleItem->total_price,
            'resolution_notes' => 'Warranty claim processed',
            'handled_by' => array_values(array_filter($this->users, fn($user) => $user['role'] === 'manager'))[0]['id'] ?? 1,
            'resolved_at' => $claimDate->copy()->addDays(rand(1, 14)),
            'created_at' => $claimDate,
            'updated_at' => $claimDate,
        ]);
    }

    private function printSummary(): void
    {
        $this->command->info('📈 Sales Data Summary:');
        $this->command->info('  • Total Sales: ' . number_format(Sale::count()));
        $this->command->info('  • Total Revenue: ₱' . number_format(Sale::sum('total_amount'), 2));
        $this->command->info('  • Total Customers Used: ' . number_format(Sale::distinct('customer_id')->whereNotNull('customer_id')->count()));
        $this->command->info('  • Total Returns: ' . number_format(SaleReturn::count()));
        $this->command->info('  • Total Warranty Claims: ' . number_format(WarrantyClaim::count()));
        $this->command->info('  • Total Shifts: ' . number_format(SalesShift::count()));
        $this->command->info('  • Average Sale Value: ₱' . number_format(Sale::avg('total_amount'), 2));

        // Monthly breakdown
        $this->command->info('📊 Monthly Sales Breakdown (2024-2026):');
        $monthlySales = Sale::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as sales_count, SUM(total_amount) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        foreach ($monthlySales as $monthData) {
            $monthName = Carbon::create($monthData->year, $monthData->month, 1)->format('M Y');
            $this->command->info("    {$monthName}: {$monthData->sales_count} sales, ₱" . number_format($monthData->revenue, 2));
        }

        // Payment method breakdown
        $this->command->info('💳 Payment Method Distribution:');
        $paymentMethods = Sale::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        foreach ($paymentMethods as $payment) {
            $percentage = ($payment->count / Sale::count()) * 100;
            $this->command->info("    " . ucfirst(str_replace('_', ' ', $payment->payment_method)) .
                ": {$payment->count} (" . number_format($percentage, 1) . "%) - ₱" .
                number_format($payment->total, 2));
        }

        // Shift breakdown
        $this->command->info('📊 Shift Summary:');
        $shiftStats = SalesShift::selectRaw('
            COUNT(*) as total_shifts,
            SUM(cash_sales) as total_cash_sales,
            SUM(card_sales) as total_card_sales,
            SUM(other_sales) as total_other_sales,
            AVG(total_transactions) as avg_transactions_per_shift
        ')->first();

        if ($shiftStats) {
            $this->command->info("    Total Shifts: {$shiftStats->total_shifts}");
            $this->command->info("    Cash Sales: ₱" . number_format($shiftStats->total_cash_sales, 2));
            $this->command->info("    Card Sales: ₱" . number_format($shiftStats->total_card_sales, 2));
            $this->command->info("    Other Sales: ₱" . number_format($shiftStats->total_other_sales, 2));
            $this->command->info("    Avg Transactions/Shift: " . number_format($shiftStats->avg_transactions_per_shift, 1));
        }
    }
}

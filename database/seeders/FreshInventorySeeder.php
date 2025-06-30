<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;

class FreshInventorySeeder extends Seeder
{
    /**
     * Simplified seeder for Excel data with Description, Category, Cost, Wholesale Price, Price, Available Qty, Alt Price 1, Alt Price 2
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Simplified Inventory Seeder...');

        DB::transaction(function () {
            $this->processCSVData();
        });

        $this->command->info('✅ Simplified Inventory Seeder completed successfully!');
    }

    private function processCSVData(): void
    {
        $csvPath = database_path('seeders/INVENTORY2.csv');

        if (!file_exists($csvPath)) {
            throw new \Exception("INVENTORY2.csv not found at: {$csvPath}");
        }

        $csvData = $this->readCSV($csvPath);
        $this->command->info("📄 Read " . count($csvData) . " products from CSV");

        $this->seedCategories($csvData);
        $this->seedProducts($csvData);
        $this->seedProductInventory($csvData);
    }

    private function readCSV($path): array
    {
        $data = [];
        $file = fopen($path, 'r');
        $headers = fgetcsv($file); // Skip headers

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 8) {
                $data[] = [
                    'category' => trim($row[1]),
                    'available_qty' => !empty(trim($row[2])) ? (int) trim($row[2]) : 0,
                    'cost' => !empty(trim($row[3])) ? (float) trim($row[3]) : 0,
                    'wholesale_price' => !empty(trim($row[4])) ? (float) trim($row[4]) : 0,
                    'price' => !empty(trim($row[5])) ? (float) trim($row[5]) : 0,
                    'alt_price1' => !empty(trim($row[6])) ? (float) trim($row[6]) : null,
                    'alt_price2' => !empty(trim($row[7])) ? (float) trim($row[7]) : null,
                ];
            }
        }

        fclose($file);
        return $data;
    }

    private function seedCategories($csvData): void
    {
        $this->command->info('📂 Seeding categories...');

        $categories = array_unique(array_column($csvData, 'category'));

        foreach ($categories as $index => $categoryName) {
            if (!empty($categoryName)) {
                Category::firstOrCreate(['name' => $categoryName], [
                    'slug' => \Str::slug($categoryName),
                    'description' => "Category for {$categoryName} products",
                    'icon' => 'o-archive-box',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ Categories seeded');
    }

    private function seedProducts($csvData): void
    {
        $this->command->info('📦 Seeding products...');

        $counter = 1;
        $processed = 0;

        foreach ($csvData as $row) {
            if (empty($row['description'])) {
                continue;
            }

            $category = Category::where('name', $row['category'])->first();

            // Generate unique identifiers
            $barcode = '8901234' . str_pad($counter, 5, '0', STR_PAD_LEFT);
            $sku = 'PRD-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
            $uniqueSlug = \Str::slug($row['description']) . '-' . $counter;

            $this->command->info("Creating product #{$counter}: '{$row['description']}'");

            Product::create([
                'name' => $row['description'],
                'slug' => $uniqueSlug,
                'sku' => $sku,
                'barcode' => $barcode,
                'category_id' => $category?->id,
                'subcategory_id' => null,
                'cost_price' => $row['cost'],
                'selling_price' => $row['price'],
                'wholesale_price' => $row['wholesale_price'],
                'alt_price1' => $row['alt_price1'],
                'alt_price2' => $row['alt_price2'],
                'alt_price3' => null,
                'warranty_months' => 12,
                'min_stock_level' => 5,
                'max_stock_level' => 500,
                'reorder_point' => 10,
                'reorder_quantity' => 50,
                'status' => 'active',
                'internal_notes' => 'Imported from Excel',
            ]);

            $counter++;
            $processed++;

            if ($processed % 25 == 0) {
                $this->command->info("   ✓ Processed {$processed} products...");
            }
        }

        $this->command->info("✅ Created {$processed} products");
    }

    private function seedProductInventory($csvData): void
    {
        $this->command->info('📊 Creating inventory records...');

        // Get or create main warehouse
        $warehouse = Warehouse::firstOrCreate(['code' => 'MW001'], [
            'name' => 'Main Branch',
            'slug' => 'main-branch',
            'address' => 'Bacarra, Ilocos Norte',
            'city' => 'Bacarra',
            'manager_name' => 'Romar Lorenzo',
            'phone' => '+63 919 920 1865',
            'type' => 'main',
            'is_active' => true,
        ]);

        $products = Product::all();

        foreach ($products as $index => $product) {
            // Find corresponding CSV data for this product
            $csvRow = $csvData[$index] ?? null;
            $quantity = $csvRow['available_qty'] ?? 0;

            Inventory::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity_on_hand' => $quantity,
                'quantity_reserved' => 0,
                'average_cost' => $product->cost_price,
                'last_counted_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info("✅ Created inventory for {$products->count()} products");
    }

    private function getShelfLocations(): array
    {
        $locations = [];
        $sections = ['A', 'B', 'C', 'D'];

        foreach ($sections as $section) {
            for ($row = 1; $row <= 5; $row++) {
                for ($pos = 1; $pos <= 8; $pos++) {
                    $locations[] = "Shelf-{$section}{$row}-{$pos}";
                }
            }
        }

        return $locations;
    }
}

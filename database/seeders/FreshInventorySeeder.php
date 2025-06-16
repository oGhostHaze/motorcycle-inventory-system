<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ProductBrand;
use App\Models\MotorcycleBrand;
use App\Models\MotorcycleModel;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;

class FreshInventorySeeder extends Seeder
{
    /**
     * Fresh seeder with barcode-based unique slugs
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Fresh Inventory Seeder with Barcode Slugs...');

        DB::transaction(function () {
            $this->processCSVData();
        });

        $this->command->info('✅ Fresh Inventory Seeder completed successfully!');
    }

    private function processCSVData(): void
    {
        $csvPath = database_path('seeders/INVENTORY.csv');

        if (!file_exists($csvPath)) {
            throw new \Exception("INVENTORY.csv not found at: {$csvPath}");
        }

        $csvData = $this->readCSV($csvPath);
        $this->command->info("📄 Read " . count($csvData) . " products from CSV");

        $this->seedMasterData($csvData);
        $this->seedAllProducts($csvData);
        $this->seedProductInventory();
    }

    private function readCSV($path): array
    {
        $data = [];
        $file = fopen($path, 'r');
        $headers = fgetcsv($file); // Skip headers

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 5) {
                $data[] = [
                    'brand' => trim($row[0]),
                    'description' => trim($row[1]),
                    'category' => trim($row[2]),
                    'subcategory' => trim($row[3]),
                    'motorcycle' => trim($row[4]),
                ];
            }
        }

        fclose($file);
        return $data;
    }

    private function seedMasterData($csvData): void
    {
        $this->command->info('📂 Seeding master data...');

        // Brands
        $brands = array_unique(array_column($csvData, 'brand'));
        foreach ($brands as $brand) {
            if (!empty($brand)) {
                ProductBrand::firstOrCreate(['name' => $brand], [
                    'slug' => \Str::slug($brand),
                    'description' => "{$brand} motorcycle parts brand",
                    'is_active' => true,
                ]);
            }
        }

        // Categories
        $categories = array_unique(array_column($csvData, 'category'));
        foreach ($categories as $index => $category) {
            if (!empty($category)) {
                Category::firstOrCreate(['name' => $category], [
                    'slug' => \Str::slug($category),
                    'description' => "Category for {$category} products",
                    'icon' => 'fas fa-box',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        }

        // Subcategories
        $subcatMap = [];
        foreach ($csvData as $row) {
            if (!empty($row['category']) && !empty($row['subcategory'])) {
                $subcatMap[$row['category']][] = $row['subcategory'];
            }
        }

        foreach ($subcatMap as $categoryName => $subcategories) {
            $category = Category::where('name', $categoryName)->first();
            if ($category) {
                foreach (array_unique($subcategories) as $index => $subcategory) {
                    Subcategory::firstOrCreate([
                        'name' => $subcategory,
                        'category_id' => $category->id
                    ], [
                        'slug' => \Str::slug($subcategory),
                        'description' => "Subcategory for {$subcategory}",
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // Motorcycle Brands
        $motorcycleBrands = ['YAMAHA', 'HONDA', 'SUZUKI', 'KAWASAKI', 'UNIVERSAL'];
        foreach ($motorcycleBrands as $brand) {
            MotorcycleBrand::firstOrCreate(['name' => $brand], [
                'slug' => \Str::slug($brand),
                'description' => "{$brand} motorcycle manufacturer",
                'is_active' => true,
            ]);
        }

        // Motorcycles
        $motorcycles = array_unique(array_column($csvData, 'motorcycle'));
        foreach ($motorcycles as $motorcycle) {
            if (!empty($motorcycle)) {
                $brand = MotorcycleBrand::where('name', 'UNIVERSAL')->first();
                MotorcycleModel::firstOrCreate(['name' => $motorcycle], [
                    'brand_id' => $brand->id,
                    'slug' => \Str::slug($motorcycle),
                    'engine_type' => '4-stroke',
                    'year_from' => 2010,
                    'year_to' => 2025,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ Master data seeded');
    }

    private function seedAllProducts($csvData): void
    {
        $this->command->info('📦 Seeding products with unique barcode slugs...');

        $counter = 1;
        $processed = 0;

        foreach ($csvData as $row) {
            $brand = ProductBrand::where('name', $row['brand'])->first();
            $category = Category::where('name', $row['category'])->first();
            $subcategory = Subcategory::where('name', $row['subcategory'])
                ->where('category_id', $category?->id)->first();

            if (!$brand || !$category || !$subcategory) {
                $this->command->warn("⚠️ Skipping: {$row['description']} - missing relationships");
                continue;
            }

            // CRITICAL: Generate barcode and slug together
            $barcode = '8901234' . str_pad($counter, 5, '0', STR_PAD_LEFT);
            $uniqueSlug = \Str::slug($row['description']) . '-' . \Str::slug($row['brand']) . '-' . $barcode;

            // Debug output
            $this->command->info("Creating product #{$counter}: '{$row['description']}' with slug: '{$uniqueSlug}'");

            $product = Product::create([
                'name' => $row['description'],
                'slug' => $uniqueSlug,
                'sku' => strtoupper(substr($row['brand'], 0, 3)) . '-' . str_pad($counter, 6, '0', STR_PAD_LEFT),
                'barcode' => $barcode,
                'description' => "Premium {$row['description']} from {$row['brand']} - Compatible with {$row['motorcycle']}",
                'category_id' => $category->id,
                'subcategory_id' => $subcategory->id,
                'product_brand_id' => $brand->id,
                'part_number' => 'PN-' . str_pad($counter, 6, '0', STR_PAD_LEFT),
                'oem_number' => 'OEM-' . strtoupper($row['brand']) . '-' . $counter,
                'cost_price' => rand(100, 5000),
                'selling_price' => rand(150, 7500),
                'wholesale_price' => rand(120, 6000),
                'weight' => rand(100, 5000) / 1000,
                'warranty_months' => 12,
                'min_stock_level' => rand(5, 25),
                'max_stock_level' => rand(100, 500),
                'reorder_point' => rand(10, 30),
                'reorder_quantity' => rand(50, 200),
                'status' => 'active',
                'internal_notes' => "CSV Import - Compatible: {$row['motorcycle']}",
            ]);

            // Link motorcycle compatibility
            $motorcycle = MotorcycleModel::where('name', $row['motorcycle'])->first();
            if ($motorcycle) {
                $product->compatibleModels()->attach($motorcycle->id, [
                    'notes' => "Compatible with {$motorcycle->name}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $counter++;
            $processed++;

            if ($processed % 25 == 0) {
                $this->command->info("   ✓ Processed {$processed} products...");
            }
        }

        $this->command->info("✅ Created {$processed} products with unique slugs");
    }

    private function seedProductInventory(): void
    {
        $this->command->info('📊 Creating inventory records...');

        // Create warehouse if needed
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'name' => 'Main Warehouse',
                'slug' => 'main-warehouse',
                'code' => 'MW001',
                'address' => 'Main Storage, Bacoor, Cavite',
                'city' => 'Bacoor',
                'manager_name' => 'Store Manager',
                'phone' => '+63-917-123-4567',
                'type' => 'main',
                'is_active' => true,
            ]);
        }

        $products = Product::all();
        $locations = $this->getShelfLocations();

        foreach ($products as $product) {
            $onHand = rand(0, 100);
            $reserved = rand(0, min(10, $onHand));

            Inventory::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity_on_hand' => $onHand,
                'quantity_reserved' => $reserved,
                'average_cost' => $product->cost_price,
                'location' => $locations[array_rand($locations)],
                'last_counted_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info("✅ Created inventory for {$products->count()} products");
    }

    private function getShelfLocations(): array
    {
        $locations = [];
        $sections = ['A', 'B', 'C', 'D', 'E'];

        foreach ($sections as $section) {
            for ($row = 1; $row <= 8; $row++) {
                for ($pos = 1; $pos <= 12; $pos++) {
                    $locations[] = "Shelf-{$section}{$row}-{$pos}";
                }
            }
        }

        return $locations;
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ProductBrand;
use App\Models\MotorcycleModel;
use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $this->seedMagsAndRims();
        $this->seedEngineOils();
        $this->seedTires();
        $this->seedCVTParts();
        $this->seedEnginePerformance();
        $this->seedCosmeticCare();
        $this->seedExhaustPipes();
    }

    private function seedMagsAndRims()
    {
        $category = Category::where('name', 'Mags and Rims')->first();
        $starSubcat = Subcategory::where('name', 'Star Series')->first();
        $bomxBrand = ProductBrand::where('name', 'BOMX')->first();
        $rcbBrand = ProductBrand::where('name', 'RCB')->first();

        $nmaxV1 = MotorcycleModel::where('name', 'NMAX V1')->first();
        $nmaxV2 = MotorcycleModel::where('name', 'NMAX V2')->first();
        $aeroxV1 = MotorcycleModel::where('name', 'Aerox V1')->first();

        $products = [
            [
                'category_id' => $category->id,
                'subcategory_id' => $starSubcat->id,
                'product_brand_id' => $bomxBrand->id,
                'name' => 'BOMX Star 14S NMAX V2 Red',
                'sku' => 'BOMX-ST14S-NV2-RED',
                'description' => 'BOMX Star 14 inch mags for NMAX V2 in Red color',
                'part_number' => 'BOMX-ST14S-001',
                'cost_price' => 5500.00,
                'selling_price' => 6800.00,
                'color' => 'Red',
                'size' => '14 inch',
                'material' => 'Aluminum Alloy',
                'warranty_months' => 12,
                'min_stock_level' => 5,
                'reorder_point' => 3,
                'reorder_quantity' => 10,
                'specifications' => [
                    'rim_size' => '14 inch',
                    'offset' => 'Standard',
                    'bolt_pattern' => '4x100',
                    'finish' => 'Powder Coated'
                ]
            ],
            [
                'category_id' => $category->id,
                'subcategory_id' => $starSubcat->id,
                'product_brand_id' => $bomxBrand->id,
                'name' => 'BOMX Star 14S NMAX V2 Blue',
                'sku' => 'BOMX-ST14S-NV2-BLU',
                'description' => 'BOMX Star 14 inch mags for NMAX V2 in Blue color',
                'part_number' => 'BOMX-ST14S-002',
                'cost_price' => 5500.00,
                'selling_price' => 6800.00,
                'color' => 'Blue',
                'size' => '14 inch',
                'material' => 'Aluminum Alloy',
                'warranty_months' => 12,
                'min_stock_level' => 5,
                'reorder_point' => 3,
                'reorder_quantity' => 10,
            ],
            [
                'category_id' => $category->id,
                'subcategory_id' => $starSubcat->id,
                'product_brand_id' => $rcbBrand->id,
                'name' => 'RCB SP 800 NMAX Bronze',
                'sku' => 'RCB-SP800-NMX-BRZ',
                'description' => 'RCB SP 800 series mags for NMAX in Bronze color',
                'part_number' => 'RCB-SP800-001',
                'cost_price' => 6000.00,
                'selling_price' => 7500.00,
                'color' => 'Bronze',
                'size' => '14 inch',
                'material' => 'Aluminum Alloy',
                'warranty_months' => 18,
                'min_stock_level' => 3,
                'reorder_point' => 2,
                'reorder_quantity' => 8,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Add compatibility
            if (str_contains($product->name, 'NMAX V2')) {
                $product->compatibleModels()->attach($nmaxV2->id);
            }
            if (str_contains($product->name, 'NMAX') && !str_contains($product->name, 'V2')) {
                $product->compatibleModels()->attach([$nmaxV1->id, $nmaxV2->id]);
            }

            // Add initial inventory
            $this->addInventory($product, rand(10, 50));
        }
    }

    private function seedEngineOils()
    {
        $category = Category::where('name', 'Engine Oil')->first();
        $engineOilSubcat = Subcategory::where('name', 'Engine Oil')->first();
        $motulBrand = ProductBrand::where('name', 'Motul')->first();
        $yamaubeBrand = ProductBrand::where('name', 'Yamalube')->first();

        $products = [
            [
                'category_id' => $category->id,
                'subcategory_id' => $engineOilSubcat->id,
                'product_brand_id' => $motulBrand->id,
                'name' => 'Motul Power LE Scooter 1L',
                'sku' => 'MOTUL-PLE-1L',
                'description' => 'Motul Power LE engine oil for scooters, 1 liter',
                'part_number' => 'MOTUL-580-1L',
                'cost_price' => 300.00,
                'selling_price' => 370.00,
                'size' => '1 Liter',
                'specifications' => [
                    'viscosity' => '15W-40',
                    'type' => 'Semi-Synthetic',
                    'api_rating' => 'SL',
                    'capacity' => '1000ml'
                ],
                'warranty_months' => 6,
                'min_stock_level' => 20,
                'reorder_point' => 10,
                'reorder_quantity' => 50,
            ],
            [
                'category_id' => $category->id,
                'subcategory_id' => $engineOilSubcat->id,
                'product_brand_id' => $yamaubeBrand->id,
                'name' => 'Yamalube Blue Core MT 20W-50 1L',
                'sku' => 'YAMALUBE-BC-MT-1L',
                'description' => 'Yamalube Blue Core engine oil for manual transmission motorcycles',
                'part_number' => 'YAM-BC-MT-1L',
                'cost_price' => 280.00,
                'selling_price' => 350.00,
                'size' => '1 Liter',
                'specifications' => [
                    'viscosity' => '20W-50',
                    'type' => 'Mineral',
                    'api_rating' => 'SL',
                    'jaso_rating' => 'MA2'
                ],
                'warranty_months' => 6,
                'min_stock_level' => 25,
                'reorder_point' => 15,
                'reorder_quantity' => 60,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            $this->addInventory($product, rand(30, 100));
        }
    }

    private function seedTires()
    {
        $category = Category::where('name', 'Tires')->first();
        $frontTireSubcat = Subcategory::where('name', 'Front Tires')->first();
        $beastireBrand = ProductBrand::where('name', 'Beastire')->first();
        $pirelliEBrand = ProductBrand::where('name', 'Pirelli')->first();

        $products = [
            [
                'category_id' => $category->id,
                'subcategory_id' => $frontTireSubcat->id,
                'product_brand_id' => $beastireBrand->id,
                'name' => 'Beastire 100-80-14 Front Tire',
                'sku' => 'BEAST-100-80-14',
                'description' => 'Beastire front tire 100/80-14',
                'part_number' => 'BEAST-100-80-14',
                'cost_price' => 1200.00,
                'selling_price' => 1500.00,
                'size' => '100/80-14',
                'specifications' => [
                    'width' => '100mm',
                    'aspect_ratio' => '80',
                    'rim_diameter' => '14 inch',
                    'load_index' => '59',
                    'speed_rating' => 'P'
                ],
                'warranty_months' => 12,
                'min_stock_level' => 10,
                'reorder_point' => 5,
                'reorder_quantity' => 20,
            ],
            [
                'category_id' => $category->id,
                'subcategory_id' => $frontTireSubcat->id,
                'product_brand_id' => $pirelliEBrand->id,
                'name' => 'Pirelli 90-90-14 Sport Tire',
                'sku' => 'PIRELLI-90-90-14',
                'description' => 'Pirelli sport tire 90/90-14',
                'part_number' => 'PIR-90-90-14',
                'cost_price' => 2000.00,
                'selling_price' => 2500.00,
                'size' => '90/90-14',
                'specifications' => [
                    'width' => '90mm',
                    'aspect_ratio' => '90',
                    'rim_diameter' => '14 inch',
                    'compound' => 'Sport',
                    'tread_pattern' => 'Directional'
                ],
                'warranty_months' => 24,
                'min_stock_level' => 5,
                'reorder_point' => 3,
                'reorder_quantity' => 12,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            $this->addInventory($product, rand(15, 40));
        }
    }

    private function seedCVTParts()
    {
        $category = Category::where('name', 'CVT Parts')->first();
        $pulleySubcat = Subcategory::where('name', 'Pulley Sets')->first();
        $jvtBrand = ProductBrand::where('name', 'JVT')->first();
        $rs8Brand = ProductBrand::where('name', 'RS8')->first();

        $products = [
            [
                'category_id' => $category->id,
                'subcategory_id' => $pulleySubcat->id,
                'product_brand_id' => $jvtBrand->id,
                'name' => 'JVT Pulley Set NMAX/AEROX',
                'sku' => 'JVT-PULLEY-NMAX',
                'description' => 'JVT CVT pulley set for NMAX and AEROX',
                'part_number' => 'JVT-CVT-001',
                'cost_price' => 3500.00,
                'selling_price' => 4200.00,
                'specifications' => [
                    'material' => 'Forged Steel',
                    'weight_reduction' => '15%',
                    'compatibility' => ['NMAX', 'AEROX'],
                    'includes' => ['Primary Pulley', 'Secondary Pulley', 'Hardware']
                ],
                'warranty_months' => 12,
                'min_stock_level' => 8,
                'reorder_point' => 4,
                'reorder_quantity' => 15,
            ],
            [
                'category_id' => $category->id,
                'subcategory_id' => $pulleySubcat->id,
                'product_brand_id' => $rs8Brand->id,
                'name' => 'RS8 Clutch Assembly Universal',
                'sku' => 'RS8-CLUTCH-UNI',
                'description' => 'RS8 universal clutch assembly',
                'part_number' => 'RS8-CLU-UNI-001',
                'cost_price' => 2800.00,
                'selling_price' => 3400.00,
                'specifications' => [
                    'type' => 'Dry Clutch',
                    'friction_material' => 'Organic',
                    'engagement_rpm' => '2500-3000',
                    'universal_fit' => true
                ],
                'warranty_months' => 6,
                'min_stock_level' => 6,
                'reorder_point' => 3,
                'reorder_quantity' => 12,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            $this->addInventory($product, rand(8, 25));
        }
    }

    private function seedEnginePerformance()
    {
        $category = Category::where('name', 'Engine Performance')->first();
        $blockSubcat = Subcategory::where('name', 'Engine Blocks')->first();
        $jvtBrand = ProductBrand::where('name', 'JVT')->first();

        $products = [
            [
                'category_id' => $category->id,
                'subcategory_id' => $blockSubcat->id,
                'product_brand_id' => $jvtBrand->id,
                'name' => 'JVT Super Head NMAX AEROX 26/23MM',
                'sku' => 'JVT-HEAD-NMX-2623',
                'description' => 'JVT Super Head for NMAX AEROX with 26/23MM valves',
                'part_number' => 'JVT-SH-2623',
                'cost_price' => 20000.00,
                'selling_price' => 24500.00,
                'size' => '26/23MM',
                'specifications' => [
                    'intake_valve' => '26mm',
                    'exhaust_valve' => '23mm',
                    'material' => 'Aluminum',
                    'porting' => 'CNC Machined',
                    'compression_ratio' => '11.5:1'
                ],
                'warranty_months' => 12,
                'min_stock_level' => 2,
                'reorder_point' => 1,
                'reorder_quantity' => 5,
                'track_serial' => true,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            $this->addInventory($product, rand(2, 8));
        }
    }

    private function seedCosmeticCare()
    {
        $category = Category::where('name', 'Cosmetic & Care')->first();
        $cleanerSubcat = Subcategory::where('name', 'Cleaners')->first();
        $mtxBrand = ProductBrand::where('name', 'MTX')->first();

        $products = [
            [
                'category_id' => $category->id,
                'subcategory_id' => $cleanerSubcat->id,
                'product_brand_id' => $mtxBrand->id,
                'name' => 'MTX Degreaser 500ml',
                'sku' => 'MTX-DEGR-500',
                'description' => 'MTX professional degreaser 500ml',
                'part_number' => 'MTX-DEG-500',
                'cost_price' => 150.00,
                'selling_price' => 200.00,
                'size' => '500ml',
                'specifications' => [
                    'type' => 'Solvent Based',
                    'application' => 'Spray',
                    'suitable_for' => ['Engine', 'Chain', 'Metal Parts']
                ],
                'warranty_months' => 0,
                'min_stock_level' => 20,
                'reorder_point' => 10,
                'reorder_quantity' => 50,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            $this->addInventory($product, rand(30, 80));
        }
    }

    private function seedExhaustPipes()
    {
        $category = Category::where('name', 'Exhaust Pipes')->first();
        $steelSubcat = Subcategory::where('name', 'Steel Pipes')->first();
        $jvtBrand = ProductBrand::where('name', 'JVT')->first();

        $products = [
            [
                'category_id' => $category->id,
                'subcategory_id' => $steelSubcat->id,
                'product_brand_id' => $jvtBrand->id,
                'name' => 'JVT NMAX/AEROX V2 Steel V3',
                'sku' => 'JVT-EXH-NMX-STV3',
                'description' => 'JVT steel exhaust pipe V3 for NMAX/AEROX V2',
                'part_number' => 'JVT-EXH-STV3',
                'cost_price' => 5500.00,
                'selling_price' => 6800.00,
                'material' => 'Stainless Steel',
                'specifications' => [
                    'material' => 'Stainless Steel 304',
                    'diameter' => '38mm',
                    'sound_level' => '85dB',
                    'weight' => '2.5kg',
                    'finish' => 'Polished'
                ],
                'warranty_months' => 12,
                'min_stock_level' => 5,
                'reorder_point' => 3,
                'reorder_quantity' => 10,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            $this->addInventory($product, rand(5, 20));
        }
    }

    private function addInventory($product, $quantity)
    {
        $mainWarehouse = Warehouse::where('code', 'MAIN01')->first();

        Inventory::create([
            'product_id' => $product->id,
            'warehouse_id' => $mainWarehouse->id,
            'quantity_on_hand' => $quantity,
            'quantity_reserved' => 0,
            'average_cost' => $product->cost_price,
            'location' => 'A' . rand(1, 10) . '-' . rand(1, 20),
        ]);
    }
}

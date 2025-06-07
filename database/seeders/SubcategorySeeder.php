<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    public function run()
    {
        $magsCategory = Category::where('name', 'Mags and Rims')->first();
        $oilCategory = Category::where('name', 'Engine Oil')->first();
        $tiresCategory = Category::where('name', 'Tires')->first();
        $cvtCategory = Category::where('name', 'CVT Parts')->first();
        $engineCategory = Category::where('name', 'Engine Performance')->first();
        $cosmeticCategory = Category::where('name', 'Cosmetic & Care')->first();
        $exhaustCategory = Category::where('name', 'Exhaust Pipes')->first();

        $subcategories = [
            // Mags and Rims
            ['category_id' => $magsCategory->id, 'name' => 'Star Series', 'sort_order' => 1],
            ['category_id' => $magsCategory->id, 'name' => 'Draco Series', 'sort_order' => 2],
            ['category_id' => $magsCategory->id, 'name' => 'Luna Series', 'sort_order' => 3],
            ['category_id' => $magsCategory->id, 'name' => 'Professional Rims', 'sort_order' => 4],

            // Engine Oil
            ['category_id' => $oilCategory->id, 'name' => 'Engine Oil', 'sort_order' => 1],
            ['category_id' => $oilCategory->id, 'name' => 'Gear Oil', 'sort_order' => 2],
            ['category_id' => $oilCategory->id, 'name' => 'Brake Fluid', 'sort_order' => 3],
            ['category_id' => $oilCategory->id, 'name' => 'Coolant', 'sort_order' => 4],

            // Tires
            ['category_id' => $tiresCategory->id, 'name' => 'Front Tires', 'sort_order' => 1],
            ['category_id' => $tiresCategory->id, 'name' => 'Rear Tires', 'sort_order' => 2],

            // CVT Parts
            ['category_id' => $cvtCategory->id, 'name' => 'Pulley Sets', 'sort_order' => 1],
            ['category_id' => $cvtCategory->id, 'name' => 'Clutch Parts', 'sort_order' => 2],
            ['category_id' => $cvtCategory->id, 'name' => 'Springs', 'sort_order' => 3],
            ['category_id' => $cvtCategory->id, 'name' => 'Flyballs', 'sort_order' => 4],

            // Engine Performance
            ['category_id' => $engineCategory->id, 'name' => 'Engine Blocks', 'sort_order' => 1],
            ['category_id' => $engineCategory->id, 'name' => 'Cylinder Heads', 'sort_order' => 2],
            ['category_id' => $engineCategory->id, 'name' => 'Fuel Injectors', 'sort_order' => 3],
            ['category_id' => $engineCategory->id, 'name' => 'Crankshafts', 'sort_order' => 4],

            // Cosmetic & Care
            ['category_id' => $cosmeticCategory->id, 'name' => 'Cleaners', 'sort_order' => 1],
            ['category_id' => $cosmeticCategory->id, 'name' => 'Spray Paints', 'sort_order' => 2],
            ['category_id' => $cosmeticCategory->id, 'name' => 'Wax & Polish', 'sort_order' => 3],

            // Exhaust Pipes
            ['category_id' => $exhaustCategory->id, 'name' => 'Steel Pipes', 'sort_order' => 1],
            ['category_id' => $exhaustCategory->id, 'name' => 'Titanium Pipes', 'sort_order' => 2],
            ['category_id' => $exhaustCategory->id, 'name' => 'Slip-on Exhausts', 'sort_order' => 3],
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create($subcategory);
        }
    }
}

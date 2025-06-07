<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Mags and Rims', 'description' => 'Motorcycle wheels and rims', 'icon' => 'heroicon-o-cog', 'sort_order' => 1],
            ['name' => 'Engine Oil', 'description' => 'Engine oils, gear oils, and lubricants', 'icon' => 'heroicon-o-beaker', 'sort_order' => 2],
            ['name' => 'Tires', 'description' => 'Motorcycle tires and tubes', 'icon' => 'heroicon-o-circle-stack', 'sort_order' => 3],
            ['name' => 'CVT Parts', 'description' => 'CVT transmission parts', 'icon' => 'heroicon-o-cog-6-tooth', 'sort_order' => 4],
            ['name' => 'Engine Performance', 'description' => 'Engine blocks, heads, and performance parts', 'icon' => 'heroicon-o-bolt', 'sort_order' => 5],
            ['name' => 'Cosmetic & Care', 'description' => 'Cleaning products and cosmetic items', 'icon' => 'heroicon-o-sparkles', 'sort_order' => 6],
            ['name' => 'Exhaust Pipes', 'description' => 'Exhaust systems and pipes', 'icon' => 'heroicon-o-funnel', 'sort_order' => 7],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

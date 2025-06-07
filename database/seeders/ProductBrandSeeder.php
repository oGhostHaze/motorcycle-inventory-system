<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ProductBrand;
use Illuminate\Database\Seeder;

class ProductBrandSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            // Mags and Rims Brands
            ['name' => 'BOMX', 'country' => 'Philippines'],
            ['name' => 'RCB', 'country' => 'Philippines'],

            // Oil Brands
            ['name' => 'Motul', 'country' => 'France'],
            ['name' => 'Shell', 'country' => 'Netherlands'],
            ['name' => 'Yamalube', 'country' => 'Japan'],
            ['name' => 'Eneos', 'country' => 'Japan'],
            ['name' => 'ZIC', 'country' => 'South Korea'],
            ['name' => 'Castrol', 'country' => 'United Kingdom'],
            ['name' => 'Petron', 'country' => 'Philippines'],
            ['name' => 'Havoline', 'country' => 'USA'],
            ['name' => 'Prestone', 'country' => 'USA'],

            // Tire Brands
            ['name' => 'Beastire', 'country' => 'Philippines'],
            ['name' => 'Eurogrip', 'country' => 'Europe'],
            ['name' => 'Gripper', 'country' => 'Philippines'],
            ['name' => 'Corsa', 'country' => 'Philippines'],
            ['name' => 'Pirelli', 'country' => 'Italy'],
            ['name' => 'Zeneos', 'country' => 'Indonesia'],
            ['name' => 'Maxxis', 'country' => 'Taiwan'],

            // CVT & Performance Brands
            ['name' => 'RS8', 'country' => 'Philippines'],
            ['name' => 'JVT', 'country' => 'Philippines'],
            ['name' => 'TSMP', 'country' => 'Philippines'],
            ['name' => 'WF', 'country' => 'Philippines'],
            ['name' => 'HIRC', 'country' => 'Philippines'],
            ['name' => 'Gracing', 'country' => 'Philippines'],
            ['name' => 'MTRT', 'country' => 'Philippines'],
            ['name' => 'TRF', 'country' => 'Philippines'],
            ['name' => 'Vormax', 'country' => 'Philippines'],

            // Cosmetic Brands
            ['name' => 'MTX', 'country' => 'Philippines'],
            ['name' => 'Koby', 'country' => 'Philippines'],
            ['name' => 'Zeno', 'country' => 'Philippines'],
            ['name' => 'Silvestre', 'country' => 'Philippines'],
            ['name' => 'Saiyan', 'country' => 'Philippines'],
            ['name' => 'Armor All', 'country' => 'USA'],
            ['name' => 'Pledge', 'country' => 'USA'],

            // Exhaust Brands
            ['name' => 'Sun Power Pipe', 'country' => 'Philippines'],
            ['name' => 'MVR', 'country' => 'Philippines'],
            ['name' => 'KUO', 'country' => 'Taiwan'],
            ['name' => 'SGP', 'country' => 'Philippines'],
        ];

        foreach ($brands as $brand) {
            ProductBrand::create($brand);
        }
    }
}

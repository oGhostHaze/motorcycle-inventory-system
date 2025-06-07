<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            MotorcycleBrandSeeder::class,
            MotorcycleModelSeeder::class,
            CategorySeeder::class,
            SubcategorySeeder::class,
            ProductBrandSeeder::class,
            WarehouseSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
        ]);
    }
}

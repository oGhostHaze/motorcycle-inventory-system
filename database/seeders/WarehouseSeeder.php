<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        $warehouses = [
            [
                'name' => 'Main Branch',
                'code' => 'MAIN01',
                'address' => 'Bacarra, Ilocos Norte',
                'city' => 'Bacarra',
                'manager_name' => 'Romar Lorenzo',
                'phone' => '+63 919 920 1865',
                'type' => 'main',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}

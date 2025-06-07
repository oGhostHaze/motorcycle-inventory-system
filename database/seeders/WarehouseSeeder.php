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
                'name' => 'Main Warehouse',
                'code' => 'MAIN01',
                'address' => 'Bacoor, Calabarzon',
                'city' => 'Bacoor',
                'manager_name' => 'John Doe',
                'phone' => '+63 917 123 4567',
                'type' => 'main',
            ],
            [
                'name' => 'Branch Store 1',
                'code' => 'BR0001',
                'address' => 'Manila City',
                'city' => 'Manila',
                'manager_name' => 'Jane Smith',
                'phone' => '+63 917 234 5678',
                'type' => 'branch',
            ],
            [
                'name' => 'Branch Store 2',
                'code' => 'BR0002',
                'address' => 'Quezon City',
                'city' => 'Quezon City',
                'manager_name' => 'Mike Johnson',
                'phone' => '+63 917 345 6789',
                'type' => 'branch',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}

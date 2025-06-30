<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            FreshInventorySeeder::class,
        ]);
    }
}

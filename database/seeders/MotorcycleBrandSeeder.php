<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\MotorcycleBrand;

class MotorcycleBrandSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            ['name' => 'Yamaha', 'description' => 'Japanese motorcycle manufacturer'],
            ['name' => 'Honda', 'description' => 'Japanese motorcycle manufacturer'],
            ['name' => 'Suzuki', 'description' => 'Japanese motorcycle manufacturer'],
            ['name' => 'Kawasaki', 'description' => 'Japanese motorcycle manufacturer'],
        ];

        foreach ($brands as $brand) {
            MotorcycleBrand::create($brand);
        }
    }
}

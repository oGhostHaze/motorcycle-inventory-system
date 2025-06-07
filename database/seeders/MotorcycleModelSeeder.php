<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\MotorcycleBrand;
use App\Models\MotorcycleModel;
use Illuminate\Database\Seeder;

class MotorcycleModelSeeder extends Seeder
{
    public function run()
    {
        $yamaha = MotorcycleBrand::where('name', 'Yamaha')->first();
        $honda = MotorcycleBrand::where('name', 'Honda')->first();

        $models = [
            // Yamaha Models
            ['brand_id' => $yamaha->id, 'name' => 'NMAX V1', 'engine_type' => '4T', 'engine_cc' => 155],
            ['brand_id' => $yamaha->id, 'name' => 'NMAX V2', 'engine_type' => '4T', 'engine_cc' => 155],
            ['brand_id' => $yamaha->id, 'name' => 'Aerox V1', 'engine_type' => '4T', 'engine_cc' => 155],
            ['brand_id' => $yamaha->id, 'name' => 'Aerox V2', 'engine_type' => '4T', 'engine_cc' => 155],
            ['brand_id' => $yamaha->id, 'name' => 'Mio M3', 'engine_type' => '4T', 'engine_cc' => 125],
            ['brand_id' => $yamaha->id, 'name' => 'Mio Sporty', 'engine_type' => '4T', 'engine_cc' => 115],
            ['brand_id' => $yamaha->id, 'name' => 'Sniper 150', 'engine_type' => '4T', 'engine_cc' => 150],

            // Honda Models
            ['brand_id' => $honda->id, 'name' => 'Click 125', 'engine_type' => '4T', 'engine_cc' => 125],
            ['brand_id' => $honda->id, 'name' => 'Click 150', 'engine_type' => '4T', 'engine_cc' => 150],
            ['brand_id' => $honda->id, 'name' => 'Click 160', 'engine_type' => '4T', 'engine_cc' => 160],
            ['brand_id' => $honda->id, 'name' => 'PCX 160', 'engine_type' => '4T', 'engine_cc' => 160],
            ['brand_id' => $honda->id, 'name' => 'ADV 150', 'engine_type' => '4T', 'engine_cc' => 150],
            ['brand_id' => $honda->id, 'name' => 'Beat', 'engine_type' => '4T', 'engine_cc' => 110],
        ];

        foreach ($models as $model) {
            MotorcycleModel::create($model);
        }
    }
}

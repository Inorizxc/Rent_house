<?php

namespace Database\Seeders;

use App\Models\HouseService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $houseServices = [
            ["house_service_id"=>"1",
            "house_id"=>"1",
            "service_id"=>"1"],
        ];
        foreach ($houseServices as $houseService) {
            HouseService::create($houseService);
        }
        $this->command->info("Создано Дом-Тэг");
        
        
    }
}

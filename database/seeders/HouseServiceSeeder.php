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
            ["HouseServiceId"=>"1","HouseId"=>"1","ServiceId"=>"1"],
        ];
        foreach ($houseServices as $houseService) {
            HouseService::create($houseService);
        }
        $this->command->info("Создано Дом-Тэг");
        
        
    }
}

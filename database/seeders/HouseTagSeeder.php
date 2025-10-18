<?php

namespace Database\Seeders;

use App\Models\HouseTag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $houseTags = [
            ["HouseTagId"=>"1","HouseId"=>"1","TagId"=>"1"],
        ];
        foreach ($houseTags as $houseTag) {
            HouseTag::create($houseTag);
        }
        $this->command->info("Создано Дом-Сервис");
        
        
    }
}

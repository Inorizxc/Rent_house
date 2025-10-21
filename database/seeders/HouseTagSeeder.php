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
            ["house_tag_id"=>"1",
            "house_id"=>"1",
            "tag_id"=>"1"],
        ];
        foreach ($houseTags as $houseTag) {
            HouseTag::create($houseTag);
        }
        $this->command->info("Создано Дом-Сервис");
        
        
    }
}

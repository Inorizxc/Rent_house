<?php

namespace Database\Seeders;

use App\Models\House;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $houses = [
            ["house_id"=>"1",
            "user_id"=>"1",
            "price_id"=>"1",
            "rent_type_id"=>"1",
            "house_type_id"=>"1",
            "calendar_id"=>"1",
            "adress"=>"Ул. Пушикна, дом Колотушкина",
            "area"=>"150 квадратов",
            "is_deleted"=>"0",
            "lng"=>"46.015446",
            "lat"=>"51.564847"],
            ["house_id"=>"2",
            "user_id"=>"1",
            "price_id"=>"1",
            "rent_type_id"=>"1",
            "house_type_id"=>"1",
            "calendar_id"=>"1",
            "adress"=>"Ул. Степана разина 93",
            "area"=>"150 квадратов",
            "is_deleted"=>"0",
            "lng"=>"46.006579",
            "lat"=>"51.547796"],
            
        ];

        foreach ($houses as $house) {

            House::create($house);
        }
        $this->command->info("Создано Пользователи");
    }
}

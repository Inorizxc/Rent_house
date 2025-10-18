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
            ["HouseId"=>"1","RentDealerId"=>"1","PriceId"=>"1","RentTypeId"=>"1","HouseTypeId"=>"1","CalendarId"=>"1","Adress"=>"Ул. Пушикна, дом Колотушкина","Area"=>"150 квадратов","Deleted"=>"None","Ing"=>"Че то","lat"=>"Че то"],
        ];
        foreach ($houses as $house) {
            House::create($house);
        }
        $this->command->info("Создано Пользователи");
    }
}

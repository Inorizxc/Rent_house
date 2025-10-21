<?php

namespace Database\Seeders;

use App\Models\HouseType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $houseTypes = [
            ["house_type_id"=>"1",
            "name"=>"Коттедж",
            "description"=>"Ну коттедж че"],
        ];
        foreach ($houseTypes as $houseType) {
            HouseType::create($houseType);
        }
        $this->command->info("Создано Вид Ренты");
        
        
    }
}

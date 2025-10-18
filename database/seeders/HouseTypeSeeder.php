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
            ["HouseTypeId"=>"1","Name"=>"Коттедж","Description"=>"Ну коттедж че"],
        ];
        foreach ($houseTypes as $houseType) {
            HouseType::create($houseType);
        }
        $this->command->info("Создано Вид Ренты");
        
        
    }
}

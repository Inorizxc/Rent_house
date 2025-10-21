<?php

namespace Database\Seeders;

use App\Models\RentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rentTypes = [
            ["rent_type_id"=>"1",
            "name"=>"Аренда",
            "description"=>"Ну аренда че"],
        ];
        foreach ($rentTypes as $rentType) {
            RentType::create($rentType);
        }
        $this->command->info("Создано Вид Ренты");
        
        
    }
}

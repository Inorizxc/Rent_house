<?php

namespace Database\Seeders;

use App\Models\PriceList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $priceLists = [
            ["PriceListId"=>"1","Price"=>"15000"],
        ];
        foreach ($priceLists as $priceList) {
            PriceList::create($priceList);
        }
        $this->command->info("Создано Прайс Лист");
    }
}

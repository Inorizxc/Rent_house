<?php

namespace Database\Seeders;

use App\Models\HouseCalendar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseCalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $orders = [
            ["house_calendar_id"=>"1",
            "house_id"=>"1",
            "dates"=>["2025-11-25",
            "2025-11-26",
            "2025-11-27",
            "2025-11-28",],
            ],
        ];
        foreach ($orders as $order) {
            HouseCalendar::create($order);
        }
        $this->command->info("Создано Календарь");
    }
}

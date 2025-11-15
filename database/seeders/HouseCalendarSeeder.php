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
            "first_date"=>"01.01.2000",
            "second_date"=>"02.01.2000",],
        ];
        foreach ($orders as $order) {
            HouseCalendar::create($order);
        }
        $this->command->info("Создано Календарь");
    }
}

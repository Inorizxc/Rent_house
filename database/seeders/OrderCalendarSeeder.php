<?php

namespace Database\Seeders;

use App\Models\OrderCalendar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderCalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $orders = [
            ["order_calendar_id"=>"1",
            "house_id"=>"1",
            "order_date"=>"01.01.2000"],
        ];
        foreach ($orders as $order) {
            OrderCalendar::create($order);
        }
        $this->command->info("Создано Ордер");
    }
}

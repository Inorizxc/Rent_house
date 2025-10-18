<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $orders = [
            ["IdOrder"=>"1","IdHouse"=>"1","DateOfOrder"=>"01.01.2000","DayCount"=>"2","CustomerId"=>"1","OrderStatus"=>"Ожидается"],
        ];
        foreach ($orders as $order) {
            Order::create($order);
        }
        $this->command->info("Создано Ордер");
    }
}

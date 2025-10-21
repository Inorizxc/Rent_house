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
            ["order_id"=>"1",
            "house_id"=>"1",
            "date_of_order"=>"01.01.2000",
            "day_count"=>"2",
            "customer_id"=>"1",
            "order_status"=>"Ожидается"],
        ];
        foreach ($orders as $order) {
            Order::create($order);
        }
        $this->command->info("Создано Ордер");
    }
}

<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\enum\OrderStatus;
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
            "order_status"=>OrderStatus::REFUND,
            "original_data"=>""],
        ];
        foreach ($orders as $order) {
            $order_without_original=array_slice($order,1,-2);
            $json_string=json_encode($order_without_original);
            $order["original_data"]=$json_string;

            Order::create($order);
        }
        $this->command->info("Создано Ордер");
    }
}

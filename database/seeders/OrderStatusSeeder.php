<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $orderStatuses = [
            ["order_status_id"=>"1","type"=>"Ожидается"],
            ["order_status_id"=>"2","type"=>"Услуга оказана"],
        ];
        foreach ($orderStatuses as $orderStatus) {
            OrderStatus::create($orderStatus);
        }
        $this->command->info("Создано Ордер Статус");
    }
}

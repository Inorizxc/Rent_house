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
            ["IdOrderStatus"=>"1","Type"=>"Ожидается"],
            ["IdOrderStatus"=>"2","Type"=>"Услуга оказана"],
        ];
        foreach ($orderStatuses as $orderStatus) {
            OrderStatus::create($orderStatus);
        }
        $this->command->info("Создано Ордер Статус");
    }
}

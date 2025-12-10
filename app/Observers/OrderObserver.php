<?php
namespace App\Observers;


use App\enum\OrderStatus;
use App\Models\Order;

class OrderObserver
{
    public function retrieved(Order $order): void
    {
        if ($order->date_of_order <> now()) {
            $order->order_status = OrderStatus::COMPLETED;
        }
    }

    public function saving(Order $order): void
    {
        if ($order->date_of_order <> now()) {
            $order->order_status = OrderStatus::COMPLETED;
        }
    }


   

    public function updated(Order $order): void
    {
        if ($order->date_of_order <> now()) {
            $order->order_status = OrderStatus::COMPLETED;
        }
    }

    public function restored(Order $order): void
    {
        if ($order->date_of_order <> now()) {
            $order->order_status = OrderStatus::COMPLETED;
        }
    }

    
}

<?php
namespace App\Observers;


use App\enum\OrderStatus;
use App\Models\Order;
use App\Services\OrderService\OrderService;
class OrderObserver
{
    public function retrieved(Order $order): void
    {
        if ($order->date_of_order <= now() && $order->order_status != OrderStatus::REFUND) {
            $service=app(OrderService::class);
            $service->transferFrozenFunds($order);
            $order->order_status = OrderStatus::COMPLETED;
        }
    }

    public function saving(Order $order): void
    {
        if ($order->date_of_order <= now() && $order->order_status != OrderStatus::REFUND) {
            $service=app(OrderService::class);
            $service->transferFrozenFunds($order);
            $order->order_status = OrderStatus::COMPLETED;
        }
    }
}

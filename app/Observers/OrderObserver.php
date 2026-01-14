<?php
namespace App\Observers;


use App\enum\OrderStatus;
use App\Models\Order;
use App\Services\OrderService\OrderService;
class OrderObserver
{
    public function retrieved(Order $order): void
    {
        if ($order->date_of_order <= now()->addDay(1) && $order->order_status != OrderStatus::COMPLETED && $order->order_status != OrderStatus::REFUND) {
            $service=app(OrderService::class);
            $user=$order->customer;
            if($order->full_payment==false){
                if($order->total_amount == $order->day_count*$order->price){
                    $service->transferFrozenFunds($order);
                    $order->order_status = OrderStatus::COMPLETED;
                    $order->save();
                }
                else{
                    $order->total_amount = $order->day_count*$order->price-$order->day_count*$order->price*$order->prepayment/100;
                    $user->balance = (float) ($user->balance) - $order->total_amount;
                    $user->frozen_balance = (float) ($user->frozen_balance) + $order->total_amount;
                    $user->save();
                    $order->save();
                    $order->total_amount = $order->day_count*$order->price;
                    $service->transferFrozenFunds($order);
                    $order->full_payment=true;
                    $order->order_status = OrderStatus::COMPLETED;
                    $order->save();
                }
            }
            //$service->transferFrozenFunds($order);
            
        }
    }


   //public function create(Order $order): void
   //{
   //    if ($order->date_of_order <= now()->addDay(1) && $order->order_status != OrderStatus::COMPLETED || $order->order_status != OrderStatus::REFUND) {
   //        $service=app(OrderService::class);
   //        $user=auth()->user();
   //        if($order->full_payment==false){
   //            $order->total_amount = $order->day_count*$order->price-$order->day_count*$order->price*$order->prepayment/100;
   //            $user->balance = (float) ($user->balance) - $order->total_amount;
   //            $user->frozen_balance = (float) ($user->frozen_balance) + $order->total_amount;
   //            $user->save();
   //            $order->save();
   //            $service->transferFrozenFunds($order);
   //            $order->full_payment=true;
   //            $order->order_status = OrderStatus::COMPLETED;
   //            $order->save();

   //            //$order->total_amount = $order->day_count*$order->price-$order->day_count*$order->price*$order->prepayment/100;
   //            //$user->balance = (float) ($user->balance) - $order->total_amount;
   //            //$user->frozen_balance = (float) ($user->frozen_balance) + $order->total_amount;
   //            //$user->save();
   //            //$order->save();
   //            //$service->transferFrozenFunds($order);
//
   //            //$order->total_amount = $order->day_count*$order->price;
   //            //$service->transferFrozenFunds($order);
   //            //
   //            //
   //            //
   //            
   //        }
   //        //$service->transferFrozenFunds($order);
   //        
   //    }
   //}
    public function saving(Order $order): void
    {
        //if ($order->date_of_order <= now() && $order->order_status == OrderStatus::PENDING) {
        //    $service=app(OrderService::class);
        //    $order->total_amount = $order->day_count*$order->price;
        //    $service->transferFrozenFunds($order);
        //    $order->order_status = OrderStatus::COMPLETED;
        //}
    }
}

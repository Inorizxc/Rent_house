<?php

namespace App\Services\OrderService;

use App\Models\Order;
use App\Models\House;
use App\Models\HouseCalendar;
use App\Models\TemporaryBlock;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\enum\OrderStatus;
use App\Services\ChatService\ChatService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function createOrder(array $data): Order
    {
        $originalData = [
            'house_id' => $data['house_id'],
            'date_of_order' => $data['date_of_order'],
            'day_count' => $data['day_count'],
            'customer_id' => $data['customer_id'],
        ];

        $house = House::findOrFail($data['house_id']);
        $pricePerDay = (float) $house->price_id;
        $dayCount = (int) $data['day_count'];
        $totalAmount = $pricePerDay * $dayCount;

        $customer = User::findOrFail($data['customer_id']);

        $currentBalance = (float) ($customer->balance ?? 0);
        if ($currentBalance < $totalAmount) {
            throw new \Exception('Недостаточно средств на балансе. Требуется: ' . number_format($totalAmount, 2, ',', ' ') . ' ₽, доступно: ' . number_format($currentBalance, 2, ',', ' ') . ' ₽');
        }

        return DB::transaction(function () use ($data, $originalData, $totalAmount, $customer) {
            $customer->balance = max(0, (float) ($customer->balance ?? 0) - $totalAmount);
            $customer->frozen_balance = (float) ($customer->frozen_balance ?? 0) + $totalAmount;
            $customer->save();

            return Order::create([
                'house_id' => $data['house_id'],
                'date_of_order' => $data['date_of_order'],
                'day_count' => $data['day_count'],
                'total_amount' => $totalAmount,
                'customer_id' => $data['customer_id'],
                'order_status_id' => $data['order_status_id'] ?? null,
                'order_status' => $data['order_status'] ?? null,
                'original_data' => json_encode($originalData),
            ]);
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        $originalData = [
            'house_id' => $data['house_id'],
            'date_of_order' => $data['date_of_order'],
            'day_count' => $data['day_count'],
            'customer_id' => $data['customer_id'],
        ];

        $order->update([
            'house_id' => $data['house_id'],
            'date_of_order' => $data['date_of_order'],
            'day_count' => $data['day_count'],
            'customer_id' => $data['customer_id'],
            'order_status_id' => $data['order_status_id'] ?? $order->order_status_id,
            'original_data' => json_encode($originalData),
        ]);

        return $order;
    }

    public function blockDates(House $house, array $dates): void
    {
        $calendar = $house->house_calendar;
        
        if ($calendar) {
            $existingDates = $calendar->dates ?? [];
            $dates = array_unique(array_merge($existingDates, $dates));
            sort($dates);
            $calendar->dates = $dates;
            $calendar->save();
        } else {
            HouseCalendar::create([
                'house_id' => $house->house_id,
                'dates' => $dates
            ]);
        }
    }

    public function getOrCreateChat($buyerId, $dealerId): Chat
    {
        $chat = $this->chatService->getUsersChat($buyerId, $dealerId);

        if (!$chat) {
            $chat = Chat::create([
                'user_id' => $buyerId,
                'rent_dealer_id' => $dealerId,
            ]);
        }

        return $chat;
    }

    public function sendOrderConfirmationMessage(Chat $chat, Order $order, string $checkinDate, string $checkoutDate, int $dayCount, int $userId): void
    {
        
        $checkinFormatted = Carbon::parse($checkinDate)->format('d.m.Y');
        $checkoutFormatted = Carbon::parse($checkoutDate)->format('d.m.Y');
        $orderMessage = "✅ Заказ #{$order->order_id} подтвержден!\n" .
                       "Период аренды: {$checkinFormatted} - {$checkoutFormatted}\n" .
                       "Количество дней: {$dayCount}";
        Message::create([
            'chat_id' => $chat->chat_id,
            'user_id' => $userId,
            'message' => $orderMessage,
        ]);
        
        $chat->touch();
        
    }

       public function sendOrderRefundMessage(Chat $chat, Order $order): void
    {
        $user = auth()->user();
            
        $orderMessage = "На Заказ #{$order->order_id} хотят оформить возврат средств!\n";
        
        Message::create([
            'chat_id' => $chat->chat_id,
            'user_id' => $user->user_id,
            'message' => $orderMessage,
        ]);
            
        $chat->touch();    
       
    }

   public function sendOrderApproveRefundMessage(Chat $chat, Order $order): void
    {
        $user = auth()->user();
            
        $orderMessage = "Средства за Заказ #{$order->order_id} возвращены!\n";
        
        Message::create([
            'chat_id' => $chat->chat_id,
            'user_id' => $user->user_id,
            'message' => $orderMessage,
        ]);
            
        $chat->touch();    
       
    }


    function removeDatesBetween(array $dates, Carbon $startDate, int $daysAfter): array
    {

        $start = Carbon::parse($startDate);
        $end = $start->copy()->addDays($daysAfter-1);

        return array_filter($dates, function($date) use ($start, $end) {
            $current = Carbon::parse($date);

            return !($current->between($start, $end, true));
        });
    }

    public function calculateDayCount(string $checkinDate, string $checkoutDate): int
    {
        $checkin = new \DateTime($checkinDate);
        $checkout = new \DateTime($checkoutDate);
        return (int)$checkin->diff($checkout)->days;
    }

    public function getOrdersAsCustomer($userId)
    {
        return Order::where('customer_id', $userId)
            ->with(['house.photo', 'house.rent_type', 'house.house_type', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOrdersAsOwner(array $houseIds)
    {
        if (empty($houseIds)) {
            return collect();
        }

        return Order::whereIn('house_id', $houseIds)
            ->with(['house.photo', 'house.rent_type', 'house.house_type', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }


    public function transferFrozenFunds(Order $order): bool
    {
        if (!$order->total_amount || $order->total_amount <= 0) {
            
            return false;
        }

        $house = $order->house;
        if (!$house) {
            
            return false;
        }

        $seller = $house->user;
        if (!$seller) {
            
            return false;
        }

        $customer = $order->customer;
        if (!$customer) {
            
            return false;
        }

        $amount = (float) $order->total_amount;

        $customerFrozenBalance = (float) ($customer->frozen_balance ?? 0);
        if ($customerFrozenBalance < $amount) {
            return false;
        }

        try {
            DB::transaction(function () use ($customer, $seller, $amount) {
                $customer->frozen_balance = max(0, (float) ($customer->frozen_balance ?? 0) - $amount);
                $customer->save();

                $seller->balance = (float) ($seller->balance ?? 0) + $amount;
                $seller->save();
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function processOrdersWithCheckinDate(): int
    {
        $today = Carbon::today()->format('Y-m-d');
        
        $orders = Order::where('date_of_order', $today)
            ->whereNotNull('total_amount')
            ->where('total_amount', '>', 0)
            ->where('order_status', '!=', OrderStatus::CANCELLED)
            ->where('order_status', '!=', OrderStatus::REFUND)
            ->with(['house.user', 'customer'])
            ->get();

        $processed = 0;
        foreach ($orders as $order) {
            if ($this->transferFrozenFunds($order)) {
                $processed++;
            }
        }

        return $processed;
    }


    public function refundOrder(Order $order): bool
    {
        if ($order->isRefunded()) {
            return false;
        }

        if (!$order->total_amount || $order->total_amount <= 0) {
            return false;
        }

        $customer = $order->customer;
        if (!$customer) {
            return false;
        }

        $amount = (float) $order->total_amount;

        $order->load(['house.user', 'customer']);

        try {
            DB::transaction(function () use ($order, $customer, $amount) {
                $customer->refresh();

                $customerFrozenBalance = (float) ($customer->frozen_balance ?? 0);
                $customerBalance = (float) ($customer->balance ?? 0);

                if ($customerFrozenBalance >= $amount) {
                    $customer->frozen_balance = max(0, $customerFrozenBalance - $amount);
                    $customer->balance = (float) ($customer->balance ?? 0) + $amount;
                    $customer->save();

                } else {
                    $house = $order->house;
                    if (!$house) {
                        throw new \Exception('Дом не найден для заказа');
                    }

                    $seller = $house->user;
                    if (!$seller) {
                        throw new \Exception('Владелец дома не найден');
                    }

                    $seller->refresh();

                    $sellerBalance = (float) ($seller->balance ?? 0);
                    if ($sellerBalance < $amount) {
                        throw new \Exception('Недостаточно средств на балансе арендодателя для возврата. Доступно: ' . $sellerBalance . ', требуется: ' . $amount);
                    }

                    $seller->balance =  $sellerBalance - $amount;
                    $seller->save();

                    $customer->balance = (float) ($customer->balance ?? 0) + $amount;
                    $customer->save();
                }

                // Устанавливаем дату возврата
                $order->refunded_at = now();
                $order->save();
            });

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}


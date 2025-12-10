<?php

namespace App\Services\OrderService;

use App\Models\Order;
use App\Models\House;
use App\Models\HouseCalendar;
use App\Models\TemporaryBlock;
use App\Models\Chat;
use App\Models\Message;
use App\enum\OrderStatus;
use App\Services\ChatService\ChatService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Создает заказ
     */
    public function createOrder(array $data): Order
    {
        $originalData = [
            'house_id' => $data['house_id'],
            'date_of_order' => $data['date_of_order'],
            'day_count' => $data['day_count'],
            'customer_id' => $data['customer_id'],
        ];

        return Order::create([
            'house_id' => $data['house_id'],
            'date_of_order' => $data['date_of_order'],
            'day_count' => $data['day_count'],
            'customer_id' => $data['customer_id'],
            'order_status_id' => $data['order_status_id'] ?? null,
            'order_status' => $data['order_status'] ?? null,
            'seller_confirmed' => $data['seller_confirmed'] ?? false,
            'original_data' => json_encode($originalData),
        ]);
    }

    /**
     * Обновляет заказ
     */
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

    /**
     * Блокирует даты в календаре
     */
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

    /**
     * Создает или находит чат между покупателем и продавцом
     */
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

    /**
     * Отправляет сообщение о подтверждении заказа
     */
    public function sendOrderConfirmationMessage(Chat $chat, Order $order, string $checkinDate, string $checkoutDate, int $dayCount, int $userId): void
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке сообщения о заказе', [
                'chat_id' => $chat->chat_id ?? null,
                'order_id' => $order->order_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Вычисляет количество дней между датами
     */
    public function calculateDayCount(string $checkinDate, string $checkoutDate): int
    {
        $checkin = new \DateTime($checkinDate);
        $checkout = new \DateTime($checkoutDate);
        return (int)$checkin->diff($checkout)->days;
    }

    /**
     * Получает заказы пользователя как заказчика
     */
    public function getOrdersAsCustomer($userId)
    {
        return Order::where('customer_id', $userId)
            ->with(['house.photo', 'house.rent_type', 'house.house_type', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получает заказы пользователя как владельца домов
     */
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

    /**
     * Вычисляет стоимость заказа
     */
    public function calculateOrderAmount(Order $order): float
    {
        if (!$order->house || !$order->house->price_id || !$order->day_count) {
            return 0;
        }

        $pricePerDay = (float) $order->house->price_id;
        return $pricePerDay * (int) $order->day_count;
    }

    /**
     * Подтверждает заказ продавцом и начисляет деньги
     */
    public function confirmBySeller(Order $order): bool
    {
        if ($order->seller_confirmed) {
            return false; // Уже подтвержден
        }

        if ($order->order_status !== OrderStatus::PENDING) {
            return false; // Неверный статус
        }

        $house = $order->house;
        if (!$house) {
            return false;
        }

        $seller = $house->user;
        if (!$seller) {
            return false;
        }

        // Вычисляем сумму заказа
        $amount = $this->calculateOrderAmount($order);

        if ($amount <= 0) {
            return false;
        }

        // Начисляем деньги продавцу
        $seller->balance = ($seller->balance ?? 0) + $amount;
        $seller->save();

        // Обновляем заказ
        $order->seller_confirmed = true;
        $order->order_status = OrderStatus::PROCESSING;
        $order->save();

        return true;
    }

    /**
     * Запрашивает возврат средств
     */
    public function requestRefund(Order $order): bool
    {
        if ($order->order_status === OrderStatus::REFUND) {
            return false; // Уже запрошен возврат
        }

        if ($order->order_status === OrderStatus::CANCELLED) {
            return false; // Заказ отменен
        }

        $order->order_status = OrderStatus::REFUND;
        $order->save();

        return true;
    }

    /**
     * Отменяет заказ покупателем (если продавец еще не подтвердил)
     */
    public function cancelByCustomer(Order $order): bool
    {
        if ($order->order_status === OrderStatus::CANCELLED) {
            return false; // Уже отменен
        }

        // Если продавец уже подтвердил, нельзя просто отменить
        if ($order->seller_confirmed) {
            return false;
        }

        $order->order_status = OrderStatus::CANCELLED;
        $order->save();

        // Освобождаем даты в календаре
        if ($order->house) {
            $this->unblockDates($order->house, $order);
        }

        return true;
    }

    /**
     * Освобождает даты в календаре при отмене заказа
     */
    public function unblockDates(House $house, Order $order): void
    {
        $calendar = $house->house_calendar;
        
        if (!$calendar || !$calendar->dates) {
            return;
        }

        // Генерируем даты заказа
        $checkinDate = new \DateTime($order->date_of_order);
        $datesToUnblock = [];
        
        for ($i = 0; $i < $order->day_count; $i++) {
            $date = clone $checkinDate;
            $date->modify("+{$i} days");
            $datesToUnblock[] = $date->format('Y-m-d');
        }

        // Удаляем даты из календаря
        $existingDates = $calendar->dates ?? [];
        $updatedDates = array_values(array_diff($existingDates, $datesToUnblock));
        
        $calendar->dates = $updatedDates;
        $calendar->save();
    }
}


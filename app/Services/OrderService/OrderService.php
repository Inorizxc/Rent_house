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

    /**
     * Создает заказ и списывает средства с баланса пользователя
     */
    public function createOrder(array $data): Order
    {
        $originalData = [
            'house_id' => $data['house_id'],
            'date_of_order' => $data['date_of_order'],
            'day_count' => $data['day_count'],
            'customer_id' => $data['customer_id'],
        ];

        // Получаем дом для вычисления стоимости
        $house = House::findOrFail($data['house_id']);
        $pricePerDay = (float) $house->price_id;
        $dayCount = (int) $data['day_count'];
        $totalAmount = $pricePerDay * $dayCount;

        // Получаем пользователя-заказчика
        $customer = User::findOrFail($data['customer_id']);

        // Проверяем баланс
        $currentBalance = (float) ($customer->balance ?? 0);
        if ($currentBalance < $totalAmount) {
            throw new \Exception('Недостаточно средств на балансе. Требуется: ' . number_format($totalAmount, 2, ',', ' ') . ' ₽, доступно: ' . number_format($currentBalance, 2, ',', ' ') . ' ₽');
        }

        // Используем транзакцию для атомарности операций
        return DB::transaction(function () use ($data, $originalData, $totalAmount, $customer) {
            // Списываем средства с balance и добавляем в frozen_balance
            $customer->balance = max(0, (float) ($customer->balance ?? 0) - $totalAmount);
            $customer->frozen_balance = (float) ($customer->frozen_balance ?? 0) + $totalAmount;
            $customer->save();

            // Создаем заказ
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
     * Переводит замороженные средства арендодателю при наступлении даты заезда
     * Списывает frozen_balance у арендатора и добавляет balance арендодателю
     */
    public function transferFrozenFunds(Order $order): bool
    {
        if (!$order->total_amount || $order->total_amount <= 0) {
            Log::warning('Попытка перевода средств для заказа без суммы', [
                'order_id' => $order->order_id
            ]);
            return false;
        }

        $house = $order->house;
        if (!$house) {
            Log::error('Дом не найден для заказа', [
                'order_id' => $order->order_id,
                'house_id' => $order->house_id
            ]);
            return false;
        }

        $seller = $house->user;
        if (!$seller) {
            Log::error('Владелец дома не найден', [
                'order_id' => $order->order_id,
                'house_id' => $house->house_id
            ]);
            return false;
        }

        $customer = $order->customer;
        if (!$customer) {
            Log::error('Заказчик не найден', [
                'order_id' => $order->order_id,
                'customer_id' => $order->customer_id
            ]);
            return false;
        }

        $amount = (float) $order->total_amount;

        // Проверяем, что у арендатора достаточно замороженных средств
        $customerFrozenBalance = (float) ($customer->frozen_balance ?? 0);
        if ($customerFrozenBalance < $amount) {
            Log::error('Недостаточно замороженных средств у арендатора', [
                'order_id' => $order->order_id,
                'customer_id' => $customer->user_id,
                'required' => $amount,
                'available' => $customerFrozenBalance
            ]);
            return false;
        }

        // Используем транзакцию для атомарности операций
        try {
            DB::transaction(function () use ($customer, $seller, $amount) {
                // Списываем замороженные средства у арендатора
                $customer->frozen_balance = max(0, (float) ($customer->frozen_balance ?? 0) - $amount);
                $customer->save();

                // Добавляем средства на баланс арендодателя
                $seller->balance = (float) ($seller->balance ?? 0) + $amount;
                $seller->save();
            });

            Log::info('Средства успешно переведены арендодателю', [
                'order_id' => $order->order_id,
                'amount' => $amount,
                'customer_id' => $customer->user_id,
                'seller_id' => $seller->user_id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка при переводе средств', [
                'order_id' => $order->order_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Обрабатывает заказы, у которых наступила дата заезда
     * Переводит замороженные средства арендодателям
     */
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

    /**
     * Возвращает средства за заказ арендатору
     * Если средства заморожены - возвращает из frozen_balance
     * Если средства уже переведены - списывает с баланса арендодателя
     */
    public function refundOrder(Order $order): bool
    {
        // Проверяем, не был ли возврат уже выполнен
        if ($order->isRefunded()) {
            Log::warning('Попытка повторного возврата средств', [
                'order_id' => $order->order_id,
                'refunded_at' => $order->refunded_at
            ]);
            return false;
        }

        if (!$order->total_amount || $order->total_amount <= 0) {
            Log::warning('Попытка возврата средств для заказа без суммы', [
                'order_id' => $order->order_id
            ]);
            return false;
        }

        $customer = $order->customer;
        if (!$customer) {
            Log::error('Заказчик не найден', [
                'order_id' => $order->order_id,
                'customer_id' => $order->customer_id
            ]);
            return false;
        }

        $amount = (float) $order->total_amount;
        
        // Загружаем связи для проверки состояния средств
        $order->load(['house.user', 'customer']);

        // Используем транзакцию для атомарности операций
        try {
            DB::transaction(function () use ($order, $customer, $amount) {
                // Обновляем данные пользователя из БД для актуальных балансов
                $customer->refresh();
                
                // Проверяем фактическое состояние средств, а не статус заказа
                $customerFrozenBalance = (float) ($customer->frozen_balance ?? 0);
                $customerBalance = (float) ($customer->balance ?? 0);
                
                Log::info('Проверка состояния средств для возврата', [
                    'order_id' => $order->order_id,
                    'amount' => $amount,
                    'customer_id' => $customer->user_id,
                    'customer_frozen_balance' => $customerFrozenBalance,
                    'customer_balance' => $customerBalance,
                    'order_status' => $order->order_status->value
                ]);
                
                // Если есть замороженные средства - возвращаем их
                if ($customerFrozenBalance >= $amount) {
                    // Возвращаем средства из frozen_balance на balance
                    $customer->frozen_balance = max(0, $customerFrozenBalance - $amount);
                    $customer->balance = (float) ($customer->balance ?? 0) + $amount;
                    $customer->save();

                    Log::info('Средства возвращены арендатору (из замороженных средств)', [
                        'order_id' => $order->order_id,
                        'amount' => $amount,
                        'customer_id' => $customer->user_id
                    ]);
                } else {
                    // Если замороженных средств нет или недостаточно - списываем с баланса арендодателя
                    $house = $order->house;
                    if (!$house) {
                        throw new \Exception('Дом не найден для заказа');
                    }

                    $seller = $house->user;
                    if (!$seller) {
                        throw new \Exception('Владелец дома не найден');
                    }

                    // Обновляем данные арендодателя из БД для актуального баланса
                    $seller->refresh();
                    
                    // Проверяем баланс арендодателя
                    $sellerBalance = (float) ($seller->balance ?? 0);
                    if ($sellerBalance < $amount) {
                        throw new \Exception('Недостаточно средств на балансе арендодателя для возврата. Доступно: ' . $sellerBalance . ', требуется: ' . $amount);
                    }

                    // Списываем средства с баланса арендодателя
                    $seller->balance = max(0, $sellerBalance - $amount);
                    $seller->save();

                    // Возвращаем средства на баланс арендатора
                    $customer->balance = (float) ($customer->balance ?? 0) + $amount;
                    $customer->save();

                    Log::info('Средства возвращены арендатору (списаны с баланса арендодателя)', [
                        'order_id' => $order->order_id,
                        'amount' => $amount,
                        'customer_id' => $customer->user_id,
                        'seller_id' => $seller->user_id
                    ]);
                }

                // Устанавливаем дату возврата
                $order->refunded_at = now();
                $order->save();
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка при возврате средств', [
                'order_id' => $order->order_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}


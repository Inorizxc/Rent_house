<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\House;
use App\Models\HouseCalendar;
use App\enum\OrderStatus;
use App\Models\TemporaryBlock;
use App\Models\Chat;
use App\Models\Message;
use App\Services\ChatService\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $query = Order::with(['house', 'user', 'order_status']);

        // Фильтрация по пользователю (если нужно)
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Фильтрация по дому
        if ($request->has('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        // Фильтрация по статусу
        if ($request->has('order_status_id')) {
            $query->where('order_status_id', $request->order_status_id);
        }

        $orders = $query->orderBy('date_of_order', 'desc')->paginate(15);

        return view('orders.index', [
            'orders' => $orders,
            'orderStatuses' => OrderStatus::cases()
        ]);
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $houses = House::where('is_deleted', false)->get();
        $orderStatuses = OrderStatus::all();
        
        return view('orders.create', [
            'houses' => $houses,
            'orderStatuses' => $orderStatuses
        ]);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,house_id',
            'date_of_order' => 'required|date',
            'day_count' => 'required|integer|min:1',
            'customer_id' => 'required|exists:users,user_id',
            'order_status_id' => 'required|exists:order_statuses,order_status_id',
        ]);

        // Создаем массив для original_data
        $originalData = [
            'house_id' => $validated['house_id'],
            'date_of_order' => $validated['date_of_order'],
            'day_count' => $validated['day_count'],
            'customer_id' => $validated['customer_id'],
        ];

        $order = Order::create([
            'house_id' => $validated['house_id'],
            'date_of_order' => $validated['date_of_order'],
            'day_count' => $validated['day_count'],
            'customer_id' => $validated['customer_id'],
            'order_status_id' => $validated['order_status_id'],
            'original_data' => json_encode($originalData),
        ]);

        return redirect()->route('orders.show', $order->order_id)
            ->with('success', 'Заказ успешно создан');
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = Order::with(['house', 'user', 'order_status'])->findOrFail($id);
        
        return view('orders.show', [
            'order' => $order
        ]);
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $houses = House::where('is_deleted', false)->get();
        $orderStatuses = OrderStatus::all();
        
        return view('orders.edit', [
            'order' => $order,
            'houses' => $houses,
            'orderStatuses' => $orderStatuses
        ]);
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'house_id' => 'required|exists:houses,house_id',
            'date_of_order' => 'required|date',
            'day_count' => 'required|integer|min:1',
            'customer_id' => 'required|exists:users,user_id',
            'order_status_id' => 'required|exists:order_statuses,order_status_id',
        ]);

        // Обновляем original_data
        $originalData = [
            'house_id' => $validated['house_id'],
            'date_of_order' => $validated['date_of_order'],
            'day_count' => $validated['day_count'],
            'customer_id' => $validated['customer_id'],
        ];

        $order->update([
            'house_id' => $validated['house_id'],
            'date_of_order' => $validated['date_of_order'],
            'day_count' => $validated['day_count'],
            'customer_id' => $validated['customer_id'],
            'order_status_id' => $validated['order_status_id'],
            'original_data' => json_encode($originalData),
        ]);

        return redirect()->route('orders.show', $order->order_id)
            ->with('success', 'Заказ успешно обновлен');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Заказ успешно удален');
    }

    /**
     * Create order from chat (for booking dates) - создает временную блокировку и редиректит на подтверждение
     */
    public function createFromChat(Request $request, $houseId)
    {
        $validated = $request->validate([
            'checkin_date' => 'required|date|after_or_equal:today',
            'checkout_date' => 'required|date|after:checkin_date',
        ]);

        $house = House::findOrFail($houseId);
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }

        $checkin = new \DateTime($validated['checkin_date']);
        $checkout = new \DateTime($validated['checkout_date']);
        $checkoutForCheck = clone $checkout;
        $checkoutForCheck->modify('-1 day');

        $datesToBlock = [];
        $current = clone $checkin;
        while ($current <= $checkoutForCheck) {
            $datesToBlock[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }

        // Очищаем истекшие блокировки
        TemporaryBlock::cleanExpired();

        // Проверяем доступность дат (учитывая постоянные и временные блокировки)
        $calendar = $house->house_calendar;
        $bookedDates = $calendar ? ($calendar->dates ?? []) : [];
        
        // Получаем активные временные блокировки для этого дома
        $temporaryBlocks = TemporaryBlock::where('house_id', $houseId)
            ->where('expires_at', '>', now())
            ->get();
        
        $temporaryBlockedDates = [];
        foreach ($temporaryBlocks as $block) {
            $temporaryBlockedDates = array_merge($temporaryBlockedDates, $block->dates ?? []);
        }
        $temporaryBlockedDates = array_unique($temporaryBlockedDates);

        // Проверяем, не заняты ли даты
        foreach ($datesToBlock as $date) {
            if (in_array($date, $bookedDates)) {
                return response()->json([
                    'success' => false,
                    'error' => "Дата {$date} уже занята"
                ], 400);
            }
            // Проверяем временные блокировки других пользователей
            if (in_array($date, $temporaryBlockedDates)) {
                $blockingUser = TemporaryBlock::where('house_id', $houseId)
                    ->where('expires_at', '>', now())
                    ->whereJsonContains('dates', $date)
                    ->where('user_id', '!=', $user->user_id)
                    ->first();
                
                if ($blockingUser) {
                    return response()->json([
                        'success' => false,
                        'error' => "Дата {$date} временно заблокирована другим пользователем"
                    ], 400);
                }
            }
        }

        // Удаляем старые временные блокировки этого пользователя для этого дома
        TemporaryBlock::where('house_id', $houseId)
            ->where('user_id', $user->user_id)
            ->delete();

        // Создаем новую временную блокировку на 10 минут
        $temporaryBlock = TemporaryBlock::create([
            'house_id' => $house->house_id,
            'user_id' => $user->user_id,
            'dates' => $datesToBlock,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Редиректим на страницу подтверждения
        $redirectUrl = route('house.order.confirm.show', $houseId) . '?' . http_build_query([
            'checkin_date' => $validated['checkin_date'],
            'checkout_date' => $validated['checkout_date']
        ]);
        
        return response()->json([
            'success' => true,
            'redirect' => $redirectUrl
        ]);
    }

    /**
     * Показать страницу подтверждения заказа с временной блокировкой
     */
    public function showConfirm(Request $request, $houseId)
    {
        $request->validate([
            'checkin_date' => 'required|date|after_or_equal:today',
            'checkout_date' => 'required|date|after:checkin_date',
        ]);

        $house = House::with(['user', 'photo'])->findOrFail($houseId);
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $checkin = new \DateTime($request->checkin_date);
        $checkout = new \DateTime($request->checkout_date);
        $checkoutForCheck = clone $checkout;
        $checkoutForCheck->modify('-1 day');

        // Получаем все даты в периоде
        $datesToBlock = [];
        $current = clone $checkin;
        while ($current <= $checkoutForCheck) {
            $datesToBlock[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }

        // Очищаем истекшие блокировки
        TemporaryBlock::cleanExpired();

        // Проверяем доступность дат (учитывая постоянные и временные блокировки)
        $calendar = $house->house_calendar;
        $bookedDates = $calendar ? ($calendar->dates ?? []) : [];
        
        // Получаем активные временные блокировки для этого дома
        $temporaryBlocks = TemporaryBlock::where('house_id', $houseId)
            ->where('expires_at', '>', now())
            ->get();
        
        $temporaryBlockedDates = [];
        foreach ($temporaryBlocks as $block) {
            $temporaryBlockedDates = array_merge($temporaryBlockedDates, $block->dates ?? []);
        }
        $temporaryBlockedDates = array_unique($temporaryBlockedDates);

        // Проверяем, не заняты ли даты
        foreach ($datesToBlock as $date) {
            if (in_array($date, $bookedDates)) {
                return redirect()->back()->with('error', "Дата {$date} уже занята");
            }
            // Проверяем временные блокировки других пользователей
            if (in_array($date, $temporaryBlockedDates)) {
                $blockingUser = TemporaryBlock::where('house_id', $houseId)
                    ->where('expires_at', '>', now())
                    ->whereJsonContains('dates', $date)
                    ->where('user_id', '!=', $user->user_id)
                    ->first();
                
                if ($blockingUser) {
                    return redirect()->back()->with('error', "Дата {$date} временно заблокирована другим пользователем");
                }
            }
        }

        // Находим временную блокировку текущего пользователя
        $temporaryBlock = TemporaryBlock::where('house_id', $houseId)
            ->where('user_id', $user->user_id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$temporaryBlock) {
            return redirect()->back()->with('error', 'Временная блокировка не найдена или истекла');
        }

        // Вычисляем количество дней
        $dayCount = (int)$checkin->diff($checkout)->days;

        return view('orders.confirm', [
            'house' => $house,
            'checkin_date' => $request->checkin_date,
            'checkout_date' => $request->checkout_date,
            'day_count' => $dayCount,
            'temporary_block_id' => $temporaryBlock->temporary_block_id,
        ]);
    }

    /**
     * Подтвердить заказ (окончательная блокировка + создание заказа)
     */
    public function confirm(Request $request, $houseId)
    {
        $request->validate([
            'checkin_date' => 'required|date|after_or_equal:today',
            'checkout_date' => 'required|date|after:checkin_date',
            'temporary_block_id' => 'required|exists:temporary_blocks,temporary_block_id',
        ]);

        $house = House::with('user')->findOrFail($houseId);
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        // Проверяем, что временная блокировка принадлежит текущему пользователю
        $temporaryBlock = TemporaryBlock::where('temporary_block_id', $request->temporary_block_id)
            ->where('user_id', $user->user_id)
            ->where('house_id', $houseId)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $checkin = new \DateTime($request->checkin_date);
        $checkout = new \DateTime($request->checkout_date);
        $checkoutForCheck = clone $checkout;
        $checkoutForCheck->modify('-1 day');

        $datesToBlock = [];
        $current = clone $checkin;
        while ($current <= $checkoutForCheck) {
            $datesToBlock[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }

        // Проверяем, что даты совпадают с временной блокировкой
        $blockDates = $temporaryBlock->dates ?? [];
        sort($blockDates);
        sort($datesToBlock);
        
        if ($blockDates !== $datesToBlock) {
            $temporaryBlock->delete();
            return redirect()->back()->with('error', 'Даты не совпадают с временной блокировкой');
        }

        // Проверяем доступность дат еще раз
        $calendar = $house->house_calendar;
        $bookedDates = $calendar ? ($calendar->dates ?? []) : [];
        
        foreach ($datesToBlock as $date) {
            if (in_array($date, $bookedDates)) {
                // Удаляем временную блокировку
                $temporaryBlock->delete();
                return redirect()->back()->with('error', "Дата {$date} уже занята");
            }
        }

        // Вычисляем количество дней
        $dayCount = (int)$checkin->diff($checkout)->days;

        // Получаем статус по умолчанию (Рассмотрение)
        $defaultStatus = OrderStatus::PENDING;
        
        if (!$defaultStatus) {
            $temporaryBlock->delete();
            return redirect()->back()->with('error', 'Не найден статус заказа');
        }

        // Создаем массив для original_data
        $originalData = [
            'house_id' => $house->house_id,
            'date_of_order' => $request->checkin_date,
            'day_count' => $dayCount,
            'customer_id' => $user->user_id,
        ];

        // Создаем заказ в БД
        try {
            $order = Order::create([
                'house_id' => $house->house_id,
                'date_of_order' => $request->checkin_date,
                'day_count' => $dayCount,
                'customer_id' => $user->user_id,
                'order_status' => $defaultStatus,
                'original_data' => json_encode($originalData),
            ]);
        } catch (\Exception $e) {
            // Удаляем временную блокировку при ошибке
            $temporaryBlock->delete();
            \Log::error('Ошибка при создании заказа', [
                'house_id' => $houseId,
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Ошибка при создании заказа: ' . $e->getMessage());
        }

        // Блокируем даты в календаре окончательно
        if ($calendar) {
            $dates = $calendar->dates ?? [];
            $dates = array_unique(array_merge($dates, $datesToBlock));
            sort($dates);
            $calendar->dates = $dates;
            $calendar->save();
        } else {
            HouseCalendar::create([
                'house_id' => $house->house_id,
                'dates' => $datesToBlock
            ]);
        }

        // Удаляем временную блокировку
        $temporaryBlock->delete();

        // Находим или создаем чат между покупателем и продавцом
        $seller = $house->user;
        
        if (!$seller) {
            \Log::error('Продавец не найден для дома', ['house_id' => $houseId]);
            return redirect()->back()->with('error', 'Ошибка: продавец не найден');
        }
        
        $buyerId = $user->user_id;
        $dealerId = $seller->user_id;

        // Если покупатель и продавец - один и тот же человек, пропускаем создание чата
        if ($buyerId != $dealerId) {
            $chatService = app(ChatService::class);
            $chat = $chatService->getUsersChat($buyerId, $dealerId);

            if (!$chat) {
                $chat = Chat::create([
                    'user_id' => $buyerId,
                    'rent_dealer_id' => $dealerId,
                ]);
            }

            // Формируем сообщение о подтверждении заказа
            $checkinFormatted = Carbon::parse($request->checkin_date)->format('d.m.Y');
            $checkoutFormatted = Carbon::parse($request->checkout_date)->format('d.m.Y');
            $orderMessage = "✅ Заказ #{$order->order_id} подтвержден!\n" .
                           "Период аренды: {$checkinFormatted} - {$checkoutFormatted}\n" .
                           "Количество дней: {$dayCount}";

            // Отправляем сообщение в чат от имени покупателя
            try {
                Message::create([
                    'chat_id' => $chat->chat_id,
                    'user_id' => $user->user_id,
                    'message' => $orderMessage,
                ]);
                
                // Обновляем время обновления чата
                $chat->touch();
            } catch (\Exception $e) {
                // Логируем ошибку, но не прерываем процесс
                \Log::error('Ошибка при отправке сообщения о заказе', [
                    'chat_id' => $chat->chat_id ?? null,
                    'order_id' => $order->order_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Перенаправляем на страницу чата
        return redirect()->route('house.chat', $houseId)
            ->with('success', 'Заказ успешно создан и подтвержден!');
    }

    /**
     * Отменить временную блокировку
     */
    public function cancel(Request $request, $houseId)
    {
        $request->validate([
            'temporary_block_id' => 'required|exists:temporary_blocks,temporary_block_id',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }

        // Удаляем временную блокировку, если она принадлежит пользователю
        $temporaryBlock = TemporaryBlock::where('temporary_block_id', $request->temporary_block_id)
            ->where('user_id', $user->user_id)
            ->where('house_id', $houseId)
            ->first();

        if ($temporaryBlock) {
            $temporaryBlock->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Временная блокировка отменена'
        ]);
    }
}

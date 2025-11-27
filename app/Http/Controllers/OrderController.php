<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\House;
use App\Models\HouseCalendar;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'orderStatuses' => OrderStatus::all()
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
     * Create order from chat (for booking dates)
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

        // Проверяем доступность дат
        $calendar = $house->house_calendar;
        $bookedDates = $calendar ? ($calendar->dates ?? []) : [];
        
        $checkin = new \DateTime($validated['checkin_date']);
        $checkout = new \DateTime($validated['checkout_date']);
        
        // Дата выезда не включается в период, поэтому вычитаем 1 день для проверки занятости
        $checkoutForCheck = clone $checkout;
        $checkoutForCheck->modify('-1 day');

        $datesToCheck = [];
        $current = clone $checkin;
        while ($current <= $checkoutForCheck) {
            $datesToCheck[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }

        // Проверяем, не заняты ли даты
        foreach ($datesToCheck as $date) {
            if (in_array($date, $bookedDates)) {
                return response()->json([
                    'success' => false,
                    'error' => "Дата {$date} уже занята"
                ], 400);
            }
        }

        // Вычисляем количество дней (от даты заезда до даты выезда включительно)
        // Если заезд 1 января, выезд 5 января, то это 4 дня (1, 2, 3, 4)
        $dayCount = (int)$checkin->diff($checkout)->days;

        // Получаем статус "Ожидается" или создаем заказ со статусом по умолчанию
        $defaultStatus = OrderStatus::where('type', 'Ожидается')->first();
        if (!$defaultStatus) {
            // Если статуса нет, берем первый доступный
            $defaultStatus = OrderStatus::first();
        }

        if (!$defaultStatus) {
            return response()->json([
                'success' => false,
                'error' => 'Не найден статус заказа'
            ], 500);
        }

        // Создаем массив для original_data
        $originalData = [
            'house_id' => $house->house_id,
            'date_of_order' => $validated['checkin_date'],
            'day_count' => $dayCount,
            'customer_id' => $user->user_id,
        ];

        $order = Order::create([
            'house_id' => $house->house_id,
            'date_of_order' => $validated['checkin_date'],
            'day_count' => $dayCount,
            'customer_id' => $user->user_id,
            'order_status_id' => $defaultStatus->order_status_id,
            'original_data' => json_encode($originalData),
        ]);

        // Блокируем даты в календаре
        if ($calendar) {
            $dates = $calendar->dates ?? [];
            $dates = array_unique(array_merge($dates, $datesToCheck));
            sort($dates);
            $calendar->dates = $dates;
            $calendar->save();
        } else {
            $calendar = HouseCalendar::create([
                'house_id' => $house->house_id,
                'dates' => $datesToCheck
            ]);
        }

        // Обновляем календарь для получения актуальных дат
        $calendar->refresh();
        $updatedDates = $calendar->dates ?? [];

        return response()->json([
            'success' => true,
            'order' => [
                'order_id' => $order->order_id,
                'house_id' => $order->house_id,
                'date_of_order' => $order->date_of_order,
                'day_count' => $order->day_count,
                'customer_id' => $order->customer_id,
                'order_status_id' => $order->order_status_id,
            ],
            'dates' => $updatedDates, // Возвращаем обновленные даты календаря
            'message' => 'Заказ успешно создан'
        ]);
    }
}


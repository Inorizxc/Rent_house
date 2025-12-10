<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\House;
use App\enum\OrderStatus;
use App\Services\OrderService\OrderService;
use App\Services\OrderService\OrderValidationService;
use App\Services\AuthService\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $orderService;
    protected $orderValidationService;
    protected $authService;

    public function __construct(
        OrderService $orderService,
        OrderValidationService $orderValidationService,
        AuthService $authService
    ) {
        $this->orderService = $orderService;
        $this->orderValidationService = $orderValidationService;
        $this->authService = $authService;
    }

    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $user = $this->authService->checkAuth();
        
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
        $houses = House::active()->get();
        $orderStatuses = OrderStatus::cases();
        
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

        $order = $this->orderService->createOrder($validated);

        return redirect()->route('orders.show', $order->order_id)
            ->with('success', 'Заказ успешно создан');
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = Order::with(['house.user', 'house.photo', 'customer'])->findOrFail($id);
        
        $currentUser = $this->authService->checkAuth();
        
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }
        
        // Забаненные пользователи не могут просматривать заказы
        if ($currentUser->isBanned()) {
            abort(403, 'Заблокированные пользователи не могут просматривать заказы');
        }
        
        // Проверяем доступ
        $access = $this->authService->checkOrderAccess($currentUser, $order);
        
        if (!$access['has_access']) {
            abort(403, 'У вас нет доступа к этому заказу');
        }
        
        return view('orders.show', [
            'order' => $order,
            'currentUser' => $currentUser,
            'isCustomer' => $access['is_customer'],
            'isOwner' => $access['is_owner'],
        ]);
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $houses = House::active()->get();
        $orderStatuses = OrderStatus::cases();
        
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

        $this->orderService->updateOrder($order, $validated);

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
        $user = $this->authService->checkAuth();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }
        
        // Проверяем, не забанен ли пользователь
        $banCheck = $this->authService->checkBan($user);
        if ($banCheck) {
            return response()->json([
                'success' => false,
                'error' => $banCheck['message']
            ], 403);
        }
        
        $validated = $request->validate([
            'checkin_date' => 'required|date|after_or_equal:today',
            'checkout_date' => 'required|date|after:checkin_date',
        ]);

        $house = House::findOrFail($houseId);
        
        // Проверяем, не забанен ли дом и не удален ли он
        if ($house->is_deleted || $house->isBanned()) {
            abort(404, 'Дом не найден или недоступен');
        }

        // Генерируем даты для блокировки
        $datesToBlock = $this->orderValidationService->generateDatesToBlock(
            $validated['checkin_date'],
            $validated['checkout_date']
        );

        // Проверяем доступность дат
        $availability = $this->orderValidationService->checkDatesAvailability($house, $datesToBlock, $user);
        
        if (!$availability['available']) {
            return response()->json([
                'success' => false,
                'error' => $availability['error']
            ], 400);
        }

        // Создаем временную блокировку
        $this->orderValidationService->createTemporaryBlock($house, $user, $datesToBlock);

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
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        // Генерируем даты для блокировки
        $datesToBlock = $this->orderValidationService->generateDatesToBlock(
            $request->checkin_date,
            $request->checkout_date
        );

        // Проверяем доступность дат
        $availability = $this->orderValidationService->checkDatesAvailability($house, $datesToBlock, $user);
        
        if (!$availability['available']) {
            return redirect()->back()->with('error', $availability['error']);
        }

        // Находим временную блокировку текущего пользователя
        $temporaryBlock = $this->orderValidationService->findUserTemporaryBlock($houseId, $user->user_id);

        if (!$temporaryBlock) {
            return redirect()->back()->with('error', 'Временная блокировка не найдена или истекла');
        }

        // Вычисляем количество дней
        $dayCount = $this->orderService->calculateDayCount($request->checkin_date, $request->checkout_date);

        // Вычисляем оставшееся время в секундах
        $expiresAt = $temporaryBlock->expires_at;
        $remainingSeconds = max(0, now()->diffInSeconds($expiresAt, false));

        return view('orders.confirm', [
            'house' => $house,
            'checkin_date' => $request->checkin_date,
            'checkout_date' => $request->checkout_date,
            'day_count' => $dayCount,
            'temporary_block_id' => $temporaryBlock->temporary_block_id,
            'expires_at' => $expiresAt,
            'remaining_seconds' => $remainingSeconds,
        ]);
    }

    /**
     * Подтвердить заказ (окончательная блокировка + создание заказа)
     */
    public function confirm(Request $request, $houseId)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }
        
        // Проверяем, не забанен ли пользователь
        $banCheck = $this->authService->checkBan($user);
        if ($banCheck) {
            return redirect()->back()->with('error', $banCheck['message']);
        }
        
        $request->validate([
            'checkin_date' => 'required|date|after_or_equal:today',
            'checkout_date' => 'required|date|after:checkin_date',
            'temporary_block_id' => 'required|exists:temporary_blocks,temporary_block_id',
        ]);

        $house = House::with('user')->findOrFail($houseId);

        // Генерируем даты для блокировки
        $datesToBlock = $this->orderValidationService->generateDatesToBlock(
            $request->checkin_date,
            $request->checkout_date
        );

        // Проверяем и валидируем временную блокировку
        $temporaryBlock = $this->orderValidationService->validateTemporaryBlock(
            $request->temporary_block_id,
            $houseId,
            $user->user_id,
            $datesToBlock
        );

        if (!$temporaryBlock) {
            return redirect()->back()->with('error', 'Временная блокировка не найдена, истекла или даты не совпадают');
        }

        // Проверяем доступность дат еще раз
        $availability = $this->orderValidationService->checkDatesAvailability($house, $datesToBlock);
        
        if (!$availability['available']) {
            $temporaryBlock->delete();
            return redirect()->back()->with('error', $availability['error']);
        }

        // Вычисляем количество дней
        $dayCount = $this->orderService->calculateDayCount($request->checkin_date, $request->checkout_date);

        // Получаем статус по умолчанию (Рассмотрение)
        $defaultStatus = OrderStatus::PENDING;
        
        if (!$defaultStatus) {
            $temporaryBlock->delete();
            return redirect()->back()->with('error', 'Не найден статус заказа');
        }

        // Создаем заказ в БД
        try {
            $order = $this->orderService->createOrder([
                'house_id' => $house->house_id,
                'date_of_order' => $request->checkin_date,
                'day_count' => $dayCount,
                'customer_id' => $user->user_id,
                'order_status' => $defaultStatus,
            ]);
        } catch (\Exception $e) {
            // Удаляем временную блокировку при ошибке
            $temporaryBlock->delete();
            Log::error('Ошибка при создании заказа', [
                'house_id' => $houseId,
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Ошибка при создании заказа: ' . $e->getMessage());
        }

        // Блокируем даты в календаре окончательно
        $this->orderService->blockDates($house, $datesToBlock);

        // Удаляем временную блокировку
        $temporaryBlock->delete();

        // Находим или создаем чат между покупателем и продавцом
        $seller = $house->user;
        
        if (!$seller) {
            Log::error('Продавец не найден для дома', ['house_id' => $houseId]);
            return redirect()->back()->with('error', 'Ошибка: продавец не найден');
        }
        
        $buyerId = $user->user_id;
        $dealerId = $seller->user_id;

        // Если покупатель и продавец - один и тот же человек, пропускаем создание чата
        if ($buyerId != $dealerId) {
            $chat = $this->orderService->getOrCreateChat($buyerId, $dealerId);
            $this->orderService->sendOrderConfirmationMessage(
                $chat,
                $order,
                $request->checkin_date,
                $request->checkout_date,
                $dayCount,
                $user->user_id
            );
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
        $user = $this->authService->checkAuth();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }
        
        // Проверяем, не забанен ли пользователь
        $banCheck = $this->authService->checkBan($user);
        if ($banCheck) {
            return response()->json([
                'success' => false,
                'error' => $banCheck['message']
            ], 403);
        }
        
        $request->validate([
            'temporary_block_id' => 'required|exists:temporary_blocks,temporary_block_id',
        ]);

        // Удаляем временную блокировку
        $removed = $this->orderValidationService->removeTemporaryBlock(
            $request->temporary_block_id,
            $houseId,
            $user->user_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Временная блокировка отменена'
        ]);
    }

    /**
     * Подтвердить заказ продавцом (начисление денег)
     */
    public function confirmBySeller(Request $request, $orderId)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $order = Order::with(['house.user'])->findOrFail($orderId);

        // Проверяем, что пользователь является владельцем дома
        if (!$order->house || $order->house->user_id != $user->user_id) {
            abort(403, 'У вас нет прав для подтверждения этого заказа');
        }

        $result = $this->orderService->confirmBySeller($order);

        if ($result) {
            return redirect()->back()->with('success', 'Заказ подтвержден! Деньги начислены на ваш баланс.');
        } else {
            return redirect()->back()->with('error', 'Не удалось подтвердить заказ. Возможно, он уже подтвержден или имеет неверный статус.');
        }
    }

    /**
     * Запросить возврат средств
     */
    public function requestRefund(Request $request, $orderId)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $order = Order::findOrFail($orderId);

        // Проверяем, что пользователь является покупателем
        if ($order->customer_id != $user->user_id) {
            abort(403, 'У вас нет прав для запроса возврата по этому заказу');
        }

        $result = $this->orderService->requestRefund($order);

        if ($result) {
            return redirect()->back()->with('success', 'Запрос на возврат средств отправлен.');
        } else {
            return redirect()->back()->with('error', 'Не удалось отправить запрос на возврат. Возможно, он уже отправлен или заказ отменен.');
        }
    }

    /**
     * Отменить заказ покупателем (если продавец еще не подтвердил)
     */
    public function cancelByCustomer(Request $request, $orderId)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $order = Order::findOrFail($orderId);

        // Проверяем, что пользователь является покупателем
        if ($order->customer_id != $user->user_id) {
            abort(403, 'У вас нет прав для отмены этого заказа');
        }

        $result = $this->orderService->cancelByCustomer($order);

        if ($result) {
            return redirect()->back()->with('success', 'Заказ отменен.');
        } else {
            return redirect()->back()->with('error', 'Не удалось отменить заказ. Возможно, продавец уже подтвердил его выполнение.');
        }
    }
}

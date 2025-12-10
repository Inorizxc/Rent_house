<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\House;
use App\enum\OrderStatus;
use App\Services\OrderService\OrderService;
use App\Services\OrderService\OrderValidationService;
use App\Services\AuthService\AuthService;
use App\Services\ChatService\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected $orderService;
    protected $orderValidationService;
    protected $authService;

    public function __construct(
        OrderService $orderService,
        OrderValidationService $orderValidationService,
        AuthService $authService,
        ChatService $chatService,
    ) {
        $this->orderService = $orderService;
        $this->orderValidationService = $orderValidationService;
        $this->authService = $authService;
        $this->chatService = $chatService;
    }

    public function index(Request $request)
    {
        $user = $this->authService->checkAuth();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $query = Order::with(['house', 'user', 'order_status']);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        if ($request->has('order_status_id')) {
            $query->where('order_status_id', $request->order_status_id);
        }

        $orders = $query->orderBy('date_of_order', 'desc')->paginate(15);

        return view('orders.index', [
            'orders' => $orders,
            'orderStatuses' => OrderStatus::cases()
        ]);
    }


    public function create()
    {
        $houses = House::active()->get();
        $orderStatuses = OrderStatus::cases();
        
        return view('orders.create', [
            'houses' => $houses,
            'orderStatuses' => $orderStatuses
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,house_id',
            'date_of_order' => 'required|date',
            'day_count' => 'required|integer|min:1',
            'customer_id' => 'required|exists:users,user_id',
            'order_status_id' => 'required|exists:order_statuses,order_status_id',
            'price'=>'required',
        ]);

        $order = $this->orderService->createOrder($validated);

        return redirect()->route('orders.show', $order->order_id);
    }


    public function show($id)
    {
        $order = Order::with(['house.user', 'house.photo', 'customer'])->findOrFail($id);
        
        $currentUser = $this->authService->checkAuth();
        
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        if ($currentUser->isBanned()) {
            abort(403, 'Заблокированные пользователи не могут просматривать заказы');
        }

        
        if ($currentUser->isBanned()) {
            abort(403, 'Заблокированные пользователи не могут просматривать заказы');
        }
        
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

        return redirect()->route('orders.show', $order->order_id);
    }


    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Заказ успешно удален');
    }


    public function createFromChat(Request $request, $houseId)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }

        
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

        
        if ($house->is_deleted || $house->isBanned()) {
            abort(404, 'Дом не найден или недоступен');
        }

        $datesToBlock = $this->orderValidationService->generateDatesToBlock(
            $validated['checkin_date'],
            $validated['checkout_date']
        );

        $availability = $this->orderValidationService->checkDatesAvailability($house, $datesToBlock, $user);
        
        if (!$availability['available']) {
            return response()->json([
                'success' => false,
                'error' => $availability['error']
            ], 400);
        }

        $this->orderValidationService->createTemporaryBlock($house, $user, $datesToBlock);

        $redirectUrl = route('house.order.confirm.show', $houseId) . '?' . http_build_query([
            'checkin_date' => $validated['checkin_date'],
            'checkout_date' => $validated['checkout_date']
        ]);
        
        return response()->json([
            'success' => true,
            'redirect' => $redirectUrl
        ]);
    }


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

        if($user->balance < $this->orderService->calculateDayCount($request->checkin_date, $request->checkout_date)*$house->price_id){
            return redirect()->route('house.chat', $houseId)
            ->with('success', 'Денег нет у тебя нищеброд');
            //return redirect()->route('map')->with('error', 'Недостаточно средств на балансе');
        }

        $datesToBlock = $this->orderValidationService->generateDatesToBlock(
            $request->checkin_date,
            $request->checkout_date
        );

        $availability = $this->orderValidationService->checkDatesAvailability($house, $datesToBlock, $user);
        
        if (!$availability['available']) {
            return redirect()->back()->with('error', $availability['error']);
        }

        $temporaryBlock = $this->orderValidationService->findUserTemporaryBlock($houseId, $user->user_id);

        if (!$temporaryBlock) {
            return redirect()->back()->with('error', 'Временная блокировка не найдена или истекла');
        }

        $dayCount = $this->orderService->calculateDayCount($request->checkin_date, $request->checkout_date);

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

    public function confirm(Request $request, $houseId)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        
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

        
        $datesToBlock = $this->orderValidationService->generateDatesToBlock(
            $request->checkin_date,
            $request->checkout_date
        );

        $temporaryBlock = $this->orderValidationService->validateTemporaryBlock(
            $request->temporary_block_id,
            $houseId,
            $user->user_id,
            $datesToBlock
        );

        if (!$temporaryBlock) {
            return redirect()->back()->with('error', 'Временная блокировка не найдена, истекла или даты не совпадают');
        }

        $availability = $this->orderValidationService->checkDatesAvailability($house, $datesToBlock);
        
        if (!$availability['available']) {
            $temporaryBlock->delete();
            return redirect()->back()->with('error', $availability['error']);
        }

        $dayCount = $this->orderService->calculateDayCount($request->checkin_date, $request->checkout_date);

        $defaultStatus = OrderStatus::PENDING;
        
        if (!$defaultStatus) {
            $temporaryBlock->delete();
            return redirect()->back()->with('error', 'Не найден статус заказа');
        }

        try {
            $order = $this->orderService->createOrder([
                'house_id' => $house->house_id,
                'date_of_order' => $request->checkin_date,
                'day_count' => $dayCount,
                'customer_id' => $user->user_id,
                'order_status' => $defaultStatus,
                'price'=>$house->price_id
            ]);
        } catch (\Exception $e) {
            $temporaryBlock->delete();
            return redirect()->back();
        }

        $this->orderService->blockDates($house, $datesToBlock);

        $temporaryBlock->delete();

        $seller = $house->user;
        
        if (!$seller) {
            return redirect()->back();
        }
        
        $buyerId = $user->user_id;
        $dealerId = $seller->user_id;

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

        return redirect()->route('house.chat', $houseId);
    }


    public function cancel(Request $request, $houseId)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }

        
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


    public function approve($id)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login');
        }

        $order = Order::with(['house.user', 'customer'])->findOrFail($id);

        if (!$order->house || $order->house->user_id != $user->user_id) {
            abort(403, 'У вас нет прав на подтверждение этого заказа');
        }

        if ($order->order_status === OrderStatus::COMPLETED) {
            return redirect()->back();
        }

        $success = $this->orderService->transferFrozenFunds($order);

        if ($success) {
            $order->order_status = OrderStatus::COMPLETED;
            $order->save();

            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }


    public function requestRefund($id)
    {   
        
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login');
        }
        
        $order = Order::with(['house.user', 'customer'])->findOrFail($id);

        if ($order->customer_id != $user->user_id) {
            abort(403, 'У вас нет прав на запрос возврата для этого заказа');
        }

        if ($order->order_status === OrderStatus::REFUND) {
            return redirect()->back()->with('error', 'Возврат уже запрошен или выполнен');
        }

        if ($order->order_status === OrderStatus::CANCELLED) {
            return redirect()->back()->with('error', 'Заказ отменен');
        }
        
        $dealer_id = $order->house->user_id;
        $customer = $user->user_id;
        $chat = $this->chatService->getUsersChat($dealer_id,$user->user_id);
        
        $this->orderService->sendOrderRefundMessage($chat,$order);

        $order->order_status = OrderStatus::REFUND;
        $order->save();

        return redirect()->back();
    }


    public function approveRefund($id)
    {
        $user = $this->authService->checkAuth();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $order = Order::with(['house.user', 'customer'])->findOrFail($id);

        if (!$order->house || $order->house->user_id != $user->user_id) {
            abort(403, 'У вас нет прав на подтверждение возврата для этого заказа');
        }

        //if ($order->order_status !== OrderStatus::REFUND) {
        //    return redirect()->back()->with('error', 'Запрос на возврат не найден');
        //}

        if ($order->isRefunded()) {
            return redirect()->back()->with('error', 'Возврат средств уже был выполнен ранее.');
        }

        
        $dealer_id = $order->house->user_id;
        $customer_id = $order->customer->user_id;
        $chat = $this->chatService->getUsersChat($dealer_id,$customer_id);
        
        $this->orderService->sendOrderApproveRefundMessage($chat,$order);
        $calendar_obj = $order->house->house_calendar;
        $calendar = $order->house->house_calendar->dates;
        $calendar_obj->dates = $this->orderService->removeDatesBetween($calendar,Carbon::parse($order->date_of_order),$order->day_count);
        $calendar_obj->save();
        $success = $this->orderService->refundOrder($order);

        if ($success) {
            
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }
}

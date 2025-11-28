<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\House;
use App\Models\Order;
use App\Services\UserServices\UserService;
use App\Services\OrderService\OrderService;
use App\Services\VerificationService\VerificationService;

class UserController extends Controller
{
    protected $userService;
    protected $orderService;
    protected $verificationService;

    public function __construct(
        UserService $userService,
        OrderService $orderService,
        VerificationService $verificationService
    ) {
        $this->userService = $userService;
        $this->orderService = $orderService;
        $this->verificationService = $verificationService;
    }
    
    public function index(){
        $users = User::orderBy("timestamp", "desc")->get();
        return view("users.index", ["users"=>$users]);
    }
    public function show(string $id){
        // Редиректим на вкладку houses по умолчанию
        return redirect()->route('profile.tab.houses', $id);
    }

    public function tabHouses(string $id, Request $request){
        $user = $this->userService->getUserWithRoleHouse($id);

        // Используем Policy для проверки доступа (разрешает гостям просматривать)
        $currentUser = auth()->user();
        // Для гостей доступ разрешен, проверку делаем только для авторизованных
        if ($currentUser) {
            $this->authorize('view', $user);
        }

        // Проверяем, является ли текущий пользователь владельцем
        $isOwner = $currentUser && $currentUser->canEditProfile($user);

        if ($request->ajax() || $request->wantsJson()) {
            return view("users.partials.houses-tab", [
                "user" => $user,
                "houses" => $user->house,
                "isOwner" => $isOwner,
            ])->render();
        }

        // Если не AJAX запрос, возвращаем полную страницу
        return view("users.show", [
            "user" => $user,
            "houses" => $user->house,
            "isOwner" => $isOwner,
        ]);
    }

    public function tabSettings(string $id, Request $request){
        $user = $this->userService->getUserWithRoleHouse($id);

        // Проверяем доступ к приватным данным (только владелец)
        $currentUser = auth()->user();
        if (!$currentUser) {
            abort(403, 'Требуется авторизация');
        }

        // Используем Policy для проверки доступа к приватным данным
        $this->authorize('viewPrivateData', $user);

        $isOwner = true; // Если дошли сюда, значит это владелец

        if ($request->ajax() || $request->wantsJson()) {
            return view("users.partials.settings-tab", [
                "user" => $user,
                "isOwner" => $isOwner,
            ])->render();
        }

        // Если не AJAX запрос, возвращаем полную страницу
        return view("users.show", [
            "user" => $user,
            "houses" => $user->house,
            "isOwner" => $isOwner,
        ]);
    }

    public function tabOrders(string $id, Request $request){
        $user = $this->userService->getUserWithRoleHouse($id);

        // Проверяем доступ к заказам (только администратор или владелец профиля)
        $currentUser = auth()->user();
        if (!$currentUser) {
            abort(403, 'Требуется авторизация для просмотра заказов');
        }

        // Перезагружаем роли пользователя для корректной проверки бана
        $currentUser->load('roles');
        
        // Забаненные пользователи не могут просматривать заказы (проверка ДО Policy)
        if ($currentUser->isBanned()) {
            abort(403, 'Заблокированные пользователи не могут просматривать заказы');
        }

        // Используем Policy для проверки доступа к заказам (внутри Policy также есть проверка бана)
        $this->authorize('viewOrders', $user);

        // Дополнительная проверка бана после authorize (на случай, если что-то прошло)
        if ($currentUser->isBanned()) {
            abort(403, 'Заблокированные пользователи не могут просматривать заказы');
        }

        // Проверяем, является ли текущий пользователь владельцем
        $isOwner = $currentUser->canEditProfile($user);

        // Получаем заказы, где пользователь является заказчиком
        $ordersAsCustomer = $this->orderService->getOrdersAsCustomer($user->user_id);

        // Получаем заказы на дома пользователя (если он арендодатель)
        $houseIds = $user->house->pluck('house_id')->toArray();
        $ordersAsOwner = $this->orderService->getOrdersAsOwner($houseIds);

        // Объединяем заказы и убираем дубликаты
        $allOrders = $ordersAsCustomer->merge($ordersAsOwner)->unique('order_id')->sortByDesc('created_at');

        // Получаем списки заказчиков и владельцев для фильтров
        $customers = User::whereIn('user_id', $allOrders->pluck('customer_id')->unique()->filter())
            ->orderBy('name')
            ->get();
        
        $owners = User::whereIn('user_id', $allOrders->pluck('house.user_id')->unique()->filter())
            ->orderBy('name')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return view("users.partials.orders-tab", [
                "user" => $user,
                "orders" => $allOrders,
                "ordersAsCustomer" => $ordersAsCustomer,
                "ordersAsOwner" => $ordersAsOwner,
                "isOwner" => $isOwner,
                "customers" => $customers,
                "owners" => $owners,
            ])->render();
        }

        // Если не AJAX запрос, возвращаем полную страницу
        return view("users.show", [
            "user" => $user,
            "houses" => $user->house,
            "isOwner" => $isOwner,
        ]);
    }

    public function create(){
        return view('users.create');
    }

    
    public function showHouses(){
        $houses = House::with('user')->get();
        return view('users.showHouses', ['houses' => $houses]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'required|string|max:100'
        ]);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $users)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'name' => 'required|string',
            'sename' => 'required|string|max:100'
        ]);

        $users->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Обрабатывает запрос на верификацию
     */
    public function requestVerification(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Требуется авторизация'
                ], 403);
            }
            return back()->with('error', 'Требуется авторизация');
        }

        // Проверяем, может ли пользователь подать заявку
        $canRequest = $this->verificationService->canRequestVerification($user);
        
        if (!$canRequest['can_request']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $canRequest['message']
                ], 400);
            }
            return back()->with('error', $canRequest['message']);
        }

        // Подаем заявку на верификацию
        $this->verificationService->requestVerification($user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Заявка на верификацию успешно подана. Мы рассмотрим её в ближайшее время.'
            ]);
        }

        return back()->with('success', 'Заявка на верификацию успешно подана. Мы рассмотрим её в ближайшее время.');
    }
}

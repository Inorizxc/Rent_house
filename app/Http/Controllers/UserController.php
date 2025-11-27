<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\House;
use App\Models\Role;
use App\Models\Order;
use App\Services\UserServices\UserService as UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    
    public function index(){
        $users = User::orderBy("timestamp", "desc")->get();
        return view("users.index", ["users"=>$users]);
    }
    public function show(string $id){
        // Редиректим на вкладку houses по умолчанию
        return redirect()->route('profile.tab.houses', $id);
    }

    public function tabHouses(string $id, Request $request){
        $userService = app(UserService::class);
        $user = $userService->getUserWithRoleHouse($id);

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
        $userService = app(UserService::class);
        $user = $userService->getUserWithRoleHouse($id);

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
        $userService = app(UserService::class);
        $user = $userService->getUserWithRoleHouse($id);

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
        $ordersAsCustomer = Order::where('customer_id', $user->user_id)
            ->with(['house.photo', 'house.rent_type', 'house.house_type', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Получаем заказы на дома пользователя (если он арендодатель)
        $houseIds = $user->house->pluck('house_id');
        $ordersAsOwner = collect();
        if ($houseIds->isNotEmpty()) {
            $ordersAsOwner = Order::whereIn('house_id', $houseIds)
                ->with(['house.photo', 'house.rent_type', 'house.house_type', 'customer'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

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

        // Проверяем, не является ли пользователь уже арендодателем или администратором
        if ($user->isRentDealer() || $user->isAdmin()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ваш аккаунт уже верифицирован'
                ], 400);
            }
            return back()->with('error', 'Ваш аккаунт уже верифицирован');
        }

        // Проверяем, не подал ли пользователь уже заявку
        if ($user->need_verification) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ваша заявка уже находится на рассмотрении'
                ], 400);
            }
            return back()->with('error', 'Ваша заявка уже находится на рассмотрении');
        }

        // Проверяем, не заблокирован ли пользователь
        if ($user->verification_denied_until) {
            $deniedUntil = Carbon::parse($user->verification_denied_until);
            if ($deniedUntil->isFuture()) {
                $message = "Вы сможете подать заявку на верификацию после {$deniedUntil->format('d.m.Y')}";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }
                return back()->with('error', $message);
            }
        }

        // Устанавливаем флаг верификации
        $user->need_verification = true;
        
        // Проверяем, существует ли колонка, и добавляем её, если нужно
        try {
            $columns = DB::select("PRAGMA table_info(users)");
            $columnExists = false;
            foreach ($columns as $column) {
                if ($column->name === 'verification_denied_until') {
                    $columnExists = true;
                    break;
                }
            }
            
            if (!$columnExists) {
                // Добавляем колонку, если её нет
                DB::statement('ALTER TABLE users ADD COLUMN verification_denied_until DATETIME NULL');
            }
            
            $user->verification_denied_until = null;
        } catch (\Exception $e) {
            // Если не удалось добавить колонку, просто не устанавливаем значение
            // Это позволит сохранить need_verification даже если колонка не существует
        }
        
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Заявка на верификацию успешно подана. Мы рассмотрим её в ближайшее время.'
            ]);
        }

        return back()->with('success', 'Заявка на верификацию успешно подана. Мы рассмотрим её в ближайшее время.');
    }
}

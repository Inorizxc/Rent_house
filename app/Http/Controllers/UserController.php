<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\House;
use App\Models\Role;
use App\Services\UserService as UserService;

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

    public function tabOrders(string $id, Request $request){
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
            return view("users.partials.orders-tab", [
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
}

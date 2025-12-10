<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\House;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BanController extends Controller
{

    public function index(Request $request)
    {
        $type = $request->get('type', 'users'); 
        
        if ($type === 'houses') {
            return $this->housesIndex($request);
        }
        
        return $this->usersIndex($request);
    }

    private function usersIndex(Request $request)
    {
        $limit = (int) $request->get('per', 20);
        $limit = max(1, min($limit, 100));
        $page = max((int) $request->get('page', 1), 1);
        
        $query = User::with('roles')->orderBy('created_at', 'desc');
        
        $total = $query->count();
        $pages = max((int) ceil($total / $limit), 1);
        $page = min($page, $pages);
        
        $users = $query->paginate($limit, ['*'], 'page', $page);
        $expiredBans = User::where('banned_until', '<', now())
                  ->get();
        User::whereIn('id', $expiredBans->pluck('id'))
            ->update(['banned_until' => null]);
        
        return view('admin.bans', [
            'type' => 'users',
            'users' => $users,
            'houses' => collect(),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit,
        ]);
    }

    private function housesIndex(Request $request)
    {
        $limit = (int) $request->get('per', 20);
        $limit = max(1, min($limit, 100));
        $page = max((int) $request->get('page', 1), 1);
        
        $query = House::with(['user', 'photo'])->orderBy('created_at', 'desc');
        
        $total = $query->count();
        $pages = max((int) ceil($total / $limit), 1);
        $page = min($page, $pages);
        
        $houses = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();
        
        return view('admin.bans', [
            'type' => 'houses',
            'users' => collect(),
            'houses' => $houses,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit,
        ]);
    }

    public function banUser(Request $request, $userId)
    {
        $request->validate([
            'ban_type' => 'required|in:temporary,permanent',
            'ban_until' => 'required_if:ban_type,temporary|nullable|date|after:now',
        ]);

        $user = User::with('roles')->findOrFail($userId);
        $bannedRole = \App\Models\Role::where('uniq_name', 'Banned')->first();
        
        if (!$bannedRole) {
            return back()->with('error', 'Роль "Забанен" не найдена. Запустите seeder ролей.');
        }

        if (!$user->isBanned()) {
            $user->original_role_id = $user->role_id;
        }

        $user->role_id = $bannedRole->role_id;

        if ($request->input('ban_type') === 'permanent') {
            $user->banned_until = null; 
        } else {
            if ($request->has('ban_until') && $request->input('ban_until')) {
                $user->banned_until = Carbon::parse($request->input('ban_until'), 'Europe/Moscow');
            } else {
                
                    $user->banned_until = Carbon::now('Europe/Moscow')->addDays(7);
            }
        }

        $user->ban_reason = $request->input('ban_reason');

        $user->save();
        $user->refresh();

        return redirect()->route('admin.bans', ['type' => 'users', 'page' => $request->get('page', 1)])->with('status', "Пользователь #{$user->user_id} забанен.");
    }
    
    public function unbanUser($userId)
    {
        $user = User::findOrFail($userId);

        $user->unban();

        return redirect()->route('admin.bans', ['type' => 'users'])->with('status', "Пользователь #{$user->user_id} разбанен.");
    }

    public function banHouse(Request $request, $houseId)
    {
        $this->ensureBanColumnsExist();
        
        $request->validate([
            'ban_type' => 'required|in:temporary,permanent',
            'ban_until' => 'required_if:ban_type,temporary|nullable|date|after:now',
        ]);

        $house = House::findOrFail($houseId);

        if ($request->input('ban_type') === 'permanent') {
            $house->is_banned_permanently = true;
            $house->banned_until = null;
        } else {
            $house->is_banned_permanently = false;
            if ($request->has('ban_until') && $request->input('ban_until')) {
                $house->banned_until = Carbon::parse($request->input('ban_until'), 'Europe/Moscow');
            } else {

                    $house->banned_until = Carbon::now('Europe/Moscow')->addDays(7);
            }
        }

        $house->save();
        $house->refresh(); 

        return redirect()->route('admin.bans', ['type' => 'houses', 'page' => $request->get('page', 1)])->with('status', "Дом #{$house->house_id} забанен.");
    }

    public function unbanHouse($houseId)
    {
        $house = House::findOrFail($houseId);
        
        $house->is_banned_permanently = false;
        $house->banned_until = null;
        $house->save();

        return redirect()->route('admin.bans', ['type' => 'houses'])->with('status', "Дом #{$house->house_id} разбанен.");
    }

    public function deleteHouse($houseId)
    {
        $house = House::findOrFail($houseId);
        
        $house->is_deleted = true;
        $house->save();

        return back()->with('status', "Дом #{$house->house_id} удален.");
    }

    public function restoreHouse($houseId)
    {
        $house = House::findOrFail($houseId);
        
        $house->is_deleted = false;
        $house->save();

        return back()->with('status', "Дом #{$house->house_id} восстановлен.");
    }
}


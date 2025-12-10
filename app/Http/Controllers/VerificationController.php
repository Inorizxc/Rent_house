<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VerificationController extends Controller
{
    /**
     * Отображает список пользователей, ожидающих верификацию
     */
    public function index(Request $request)
    {
        $limit = (int) $request->get('per', 20);
        $limit = max(1, min($limit, 100));

        $page = max((int) $request->get('page', 1), 1);

        $query = User::where('need_verification', true)
            ->with('roles')
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $pages = max((int) ceil($total / $limit), 1);
        $page = min($page, $pages);

        $users = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return view('admin.verification', [
            'users' => $users,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit,
        ]);
    }

    /**
     * Подтверждает верификацию пользователя
     */
    public function approve(Request $request, $userId)
    {
        $user = User::with('roles')->findOrFail($userId);

        if (!$user->need_verification) {
            return back()->with('error', 'Пользователь не ожидает верификации');
        }

        // Находим роль арендодателя
        $rentDealerRole = Role::where('uniq_name', 'RentDealer')->first();

        if (!$rentDealerRole) {
            return back()->with('error', 'Роль арендодателя не найдена');
        }

        // Меняем роль на арендодателя и снимаем флаг верификации
        $user->role_id = $rentDealerRole->role_id;
        $user->need_verification = false;
        $user->verification_denied_until = null;
        $user->save();

        return back()->with('status', "Верификация пользователя #{$user->user_id} подтверждена. Роль изменена на арендодателя.");
    }

    /**
     * Отклоняет верификацию пользователя
     */
    public function reject(Request $request, $userId)
    {
        $request->validate([
            'denied_days' => 'required|integer|min:1|max:365',
        ]);

        $user = User::findOrFail($userId);

        if (!$user->need_verification) {
            return back()->with('error', 'Пользователь не ожидает верификации');
        }

        $deniedDays = (int) $request->input('denied_days', 7);
        $deniedUntil = Carbon::now()->addDays($deniedDays);

        // Снимаем флаг верификации и устанавливаем дату блокировки
        $user->need_verification = false;
        $user->verification_denied_until = $deniedUntil;
        $user->verified_deny_reason = $request->input("reject_reason");
        $user->save();

        return back()->with('status', "Верификация пользователя #{$user->user_id} отклонена. Повторная подача будет доступна после {$deniedUntil->format('d.m.Y')}.");
    }
}


<?php

namespace App\Services\VerificationService;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    /**
     * Проверяет, может ли пользователь подать заявку на верификацию
     */
    public function canRequestVerification(User $user): array
    {
        if ($user->isRentDealer() || $user->isAdmin()) {
            return [
                'can_request' => false,
                'message' => 'Ваш аккаунт уже верифицирован'
            ];
        }

        if ($user->need_verification) {
            return [
                'can_request' => false,
                'message' => 'Ваша заявка уже находится на рассмотрении'
            ];
        }

        if ($user->verification_denied_until) {
            $deniedUntil = Carbon::parse($user->verification_denied_until);
            if ($deniedUntil->isFuture()) {
                return [
                    'can_request' => false,
                    'message' => "Вы сможете подать заявку на верификацию после {$deniedUntil->format('d.m.Y')}"
                ];
            }
        }

        return ['can_request' => true];
    }

    public function requestVerification(User $user): void
    {
        $user->need_verification = true;
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
                DB::statement('ALTER TABLE users ADD COLUMN verification_denied_until DATETIME NULL');
            }
            
            $user->verification_denied_until = null;
        } catch (\Exception $e) {
        }
        
        $user->save();
    }
}


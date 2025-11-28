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
        // Проверяем, не является ли пользователь уже арендодателем или администратором
        if ($user->isRentDealer() || $user->isAdmin()) {
            return [
                'can_request' => false,
                'message' => 'Ваш аккаунт уже верифицирован'
            ];
        }

        // Проверяем, не подал ли пользователь уже заявку
        if ($user->need_verification) {
            return [
                'can_request' => false,
                'message' => 'Ваша заявка уже находится на рассмотрении'
            ];
        }

        // Проверяем, не заблокирован ли пользователь
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

    /**
     * Подает заявку на верификацию
     */
    public function requestVerification(User $user): void
    {
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
    }
}


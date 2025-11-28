<?php

namespace App\Services\AuthService;

use App\Models\User;
use Carbon\Carbon;

class AuthService
{
    /**
     * Проверяет, авторизован ли пользователь
     */
    public function checkAuth(): ?User
    {
        return auth()->user();
    }

    /**
     * Проверяет, не забанен ли пользователь
     */
    public function checkBan(User $user): ?array
    {
        if (!$user->isBanned()) {
            return null;
        }

        $banUntil = $user->getBanUntilDate();
        $banReason = $user->ban_reason ? "\n\nПричина: {$user->ban_reason}" : '';
        
        $message = $user->isBannedPermanently() 
            ? 'Ваш аккаунт заблокирован навсегда. Вы не можете выполнять это действие.' . $banReason
            : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете выполнять это действие до этой даты." . $banReason;

        return [
            'message' => $message,
            'ban_until' => $banUntil,
            'is_permanent' => $user->isBannedPermanently(),
        ];
    }

    /**
     * Проверяет доступ к чату
     */
    public function checkChatAccess(User $user, $chat): bool
    {
        return $chat->user_id == $user->user_id || $chat->rent_dealer_id == $user->user_id;
    }

    /**
     * Проверяет доступ к заказу
     */
    public function checkOrderAccess(User $user, $order): array
    {
        $isCustomer = $order->customer_id == $user->user_id;
        $isOwner = $order->house && $order->house->user_id == $user->user_id;
        $isAdmin = $user->isAdmin();

        return [
            'has_access' => $isCustomer || $isOwner || $isAdmin,
            'is_customer' => $isCustomer,
            'is_owner' => $isOwner,
            'is_admin' => $isAdmin,
        ];
    }
}


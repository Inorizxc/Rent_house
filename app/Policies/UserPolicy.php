<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Администраторы могут просматривать список пользователей
     */
    public function viewAny(?User $user): bool
    {
        // Гости могут видеть список пользователей, но с ограничениями
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Любой может просматривать профиль пользователя, но контент зависит от владения
     */
    public function view(?User $user, User $profileUser): bool
    {
        // Гости могут просматривать профили
        if (!$user) {
            return true;
        }

        // Используем метод из модели для проверки
        return $user->canViewProfile($profileUser);
    }

    /**
     * Determine whether the user can create models.
     * Обычно это регистрация, доступна всем
     */
    public function create(?User $user): bool
    {
        // Регистрация доступна всем
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Только владелец может редактировать свой профиль
     */
    public function update(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canEditProfile($profileUser);
    }

    /**
     * Determine whether the user can delete the model.
     * Только администратор может удалять пользователей
     */
    public function delete(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }

        // Только администратор может удалять пользователей
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Проверяет, может ли пользователь просматривать приватные данные профиля
     * (заказы, настройки)
     */
    public function viewPrivateData(User $user, User $profileUser): bool
    {
        return $user->canEditProfile($profileUser);
    }

    /**
     * Проверяет, может ли пользователь просматривать заказы профиля
     * Только администратор или владелец профиля могут просматривать заказы
     * Забаненные пользователи не могут просматривать заказы
     */
    public function viewOrders(?User $user, User $profileUser): bool
    {
        // Гости не могут просматривать заказы
        if (!$user) {
            return false;
        }

        // Перезагружаем роли пользователя для корректной проверки бана
        $user->load('roles');

        // Забаненные пользователи не могут просматривать заказы
        if ($user->isBanned()) {
            return false;
        }

        // Администратор может просматривать заказы любого пользователя
        if ($user->isAdmin()) {
            return true;
        }

        // Владелец профиля может просматривать свои заказы
        return $user->canEditProfile($profileUser);
    }
}

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

    public function view(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return true;
        }
        return $user->canViewProfile($profileUser);
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }
        return $user->canEditProfile($profileUser);
    }

    public function delete(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }
        return $user->isAdmin();
    }

    public function restore(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }
        return $user->isAdmin();
    }

    public function forceDelete(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }
        return $user->isAdmin();
    }

    public function viewPrivateData(User $user, User $profileUser): bool
    {
        return $user->canEditProfile($profileUser);
    }

    public function viewOrders(?User $user, User $profileUser): bool
    {
        if (!$user) {
            return false;
        }
        //$user->load('roles');
        if ($user->isBanned()) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        return $user->canEditProfile($profileUser);
    }
}

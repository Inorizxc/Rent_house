<?php

namespace App\Policies;

use App\Models\House;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HousePolicy
{
    /**
     * Determine whether the user can view any models.
     * Все могут просматривать список домов
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Все могут просматривать отдельный дом
     */
    public function view(?User $user, House $house): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Только арендодатель или администратор могут создавать дома
     */
    public function create(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canCreateHouse();
    }

    /**
     * Determine whether the user can update the model.
     * Администратор может редактировать любые дома
     * Арендодатель может редактировать только свои дома
     */
    public function update(?User $user, House $house): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canEditHouse($house);
    }

    /**
     * Determine whether the user can delete the model.
     * Администратор может удалять любые дома
     * Арендодатель может удалять только свои дома
     */
    public function delete(?User $user, House $house): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canDeleteHouse($house);
    }

    /**
     * Determine whether the user can restore the model.
     * Только администратор может восстанавливать удаленные дома
     */
    public function restore(?User $user, House $house): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Только администратор может полностью удалять дома
     */
    public function forceDelete(?User $user, House $house): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }
}

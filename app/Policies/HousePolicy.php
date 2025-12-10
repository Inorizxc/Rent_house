<?php

namespace App\Policies;

use App\Models\House;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HousePolicy
{

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, House $house): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canCreateHouse();
    }

    public function update(?User $user, House $house): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canEditHouse($house);
    }

    public function delete(?User $user, House $house): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canDeleteHouse($house);
    }

    public function restore(?User $user, House $house): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }

    public function forceDelete(?User $user, House $house): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }
}

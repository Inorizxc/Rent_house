<?php

namespace App\Observers;

use App\Models\User;

class UserBanObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if($user->banned_until!=null){
            if ($user->banned_until <= now()->addDays(7)) {
            $user->banned_until = null;
            $user->ban_reason = null;
            $user->role_id = $user->original_role_id;
        }
        }
    }

    public function retrieved(User $user): void
    {
        if($user->banned_until!=null){
            if ($user->banned_until <= now()->addDays(7)) {
                $user->banned_until = null;
                $user->ban_reason = null;
                $user->role_id = $user->original_role_id;
            }
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if($user->banned_until!=null){
            if ($user->banned_until <= now()->addDays(7)) {
            $user->banned_until = null;
            $user->ban_reason = null;
            $user->role_id = $user->original_role_id;
        }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if($user->banned_until!=null){
            if ($user->banned_until <= now()->addDays(7)) {
            $user->banned_until = null;
            $user->ban_reason = null;
            $user->role_id = $user->original_role_id;
        }
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        if($user->banned_until!=null){
            if ($user->banned_until <= now()->addDays(7)) {
            $user->banned_until = null;
            $user->ban_reason = null;
            $user->role_id = $user->original_role_id;
        }
        }
    }

    public function forceDeleted(User $user): void
    {
        if($user->banned_until!=null){
            if ($user->banned_until <= now()->addDays(7)) {
            $user->banned_until = null;
            $user->ban_reason = null;
            $user->role_id = $user->original_role_id;
        }
        }
    }
}

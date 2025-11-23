<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\House;
use App\Models\Role;

class UserService{
    
    public function getUserWithRoleHouse(string $id): User{
        return User::with([
            'roles',
            'house' => function ($query) {
                $query->with(['rent_type','house_type','photo'])
                    ->where(function ($q) {
                        $q->whereNull('is_deleted')
                            ->orWhere('is_deleted', false);
                    })
                    ->orderByDesc('house_id');
        }])->findOrFail($id);
    }

}
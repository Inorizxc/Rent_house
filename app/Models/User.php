<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Role;

class User extends Authenticatable
{
    protected $table = "users";
    protected $primaryKey = "user_id";
    public $incrementing = true;
    
    protected $fillable = [
        'user_id',
        'role_id',
        'name',
        'sename',   
        'patronymic',
        'birth_date',
        'email',
        'password',
        'phone',
        'card',
        'balance',
        'frozen_balance',
        'need_verification',
        'verification_denied_until',
        'banned_until', 
        'ban_reason', 
        'original_role_id', 
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts  = [
        'birth_date' => 'date',
        'need_verification' => 'boolean',
        'verification_denied_until' => 'datetime',
        'banned_until' => 'datetime',
        'original_role_id' => 'integer',
        'balance' => 'decimal:2',
        'frozen_balance' => 'decimal:2',
    ];

    public static function boot(){
        parent::boot();

        static::deleting(function ($user){
            //$user->house()->delete();
            $user -> foreign('user_id')->refeneces('user_id')->on('houses')->onDelete("cascade");
        });
    }

    public function roles(){
        return $this->belongsTo(Role::class,"role_id","role_id");
    }
    public function photo(){
        return $this->hasMany(Role::class,"user_id","user_id");
    }
    public function house(){
        return $this->hasMany(House::class,"user_id","user_id");
    }
    public function order(){
        return $this->hasMany(Order::class,"user_id","user_id");
    }
    
    public function ordersAsCustomer(){
        return $this->hasMany(Order::class,"customer_id","user_id");
    }

    /**
     * @param User|null $profileUser Пользователь профиля
     * @return bool
     */
    public function isOwnerOf(?User $profileUser): bool
    {
        if (!$profileUser) {
            return false;
        }

        return (int) $this->user_id === (int) $profileUser->user_id;
    }

    /**
     * @param User|null $profileUser Пользователь профиля
     * @return bool
     */
    public function canViewProfile(?User $profileUser): bool
    {
        if (!$profileUser) {
            return false;
        }

        return true;
    }

    /**
     * @param User|null 
     * @return bool
     */
    public function canEditProfile(?User $profileUser): bool
    {
        if (!$profileUser) {
            return false;
        }

        return $this->isOwnerOf($profileUser);
    }


    public function isAdmin(): bool
    {
        // Проверяем роль через связь
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles && strtolower($this->roles->uniq_name ?? '') === 'admin';
    }

 
    public function isRentDealer(): bool
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles && strtolower($this->roles->uniq_name ?? '') === 'rentdealer';
    }

    public function canCreateHouse(): bool
    {
        return $this->isAdmin() || $this->isRentDealer();
    }

    public function isHouseOwner(\App\Models\House $house): bool
    {
        return (int) $this->user_id === (int) $house->user_id;
    }

    public function canEditHouse(\App\Models\House $house): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isRentDealer() && $this->isHouseOwner($house)) {
            return true;
        }

        return false;
    }

    public function canDeleteHouse(\App\Models\House $house): bool
    {

        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isRentDealer() && $this->isHouseOwner($house)) {
            return true;
        }

        return false;
    }

    public function isBanned(): bool
    {
        if (!$this->roles) {
            $this->load('roles');
        }
        
        $bannedRole = Role::where('uniq_name', 'Banned')->first();
        if (!$bannedRole) {
            return false;
        }
        
        if ($this->role_id == $bannedRole->role_id) {
<<<<<<< HEAD
            // ЗАКОММЕНТИРОВАНО: Автоматическая проверка и разбан истекших банов
            // Для временного бана проверяем дату окончания
            // if ($this->banned_until) {
            //     if ($this->banned_until instanceof \Carbon\Carbon) {
            //         // Если дата прошла, автоматически разбаниваем
            //         if ($this->banned_until->isPast()) {
            //             $this->unban();
            //             return false;
            //         }
            //         return true;
            //     }
            //     $banDate = \Carbon\Carbon::parse($this->banned_until, 'Europe/Moscow');
            //     if ($banDate->isPast()) {
            //         $this->unban();
            //         return false;
            //     }
            //     return true;
            // }
            
            // Временный бан - проверяем только дату без автоматического разбана
            if ($this->banned_until) {
                if ($this->banned_until instanceof \Carbon\Carbon) {
                    return !$this->banned_until->isPast();
=======
            if ($this->banned_until) {
                if ($this->banned_until instanceof \Carbon\Carbon) {
                    if ($this->banned_until->isPast()) {
                        $this->unban();
                        return false;
                    }
                    return true;
>>>>>>> 5aa33b28e8c427fe9f510799b248d1be5651b98f
                }
                $banDate = \Carbon\Carbon::parse($this->banned_until, 'Europe/Moscow');
                return !$banDate->isPast();
            }
<<<<<<< HEAD
            
            // Постоянный бан (нет даты окончания)
=======
>>>>>>> 5aa33b28e8c427fe9f510799b248d1be5651b98f
            return true;
        }
        
        return false;
    }

    public function unban()
    {
        $bannedRole = Role::where('uniq_name', 'Banned')->first();
        if (!$bannedRole || $this->role_id != $bannedRole->role_id) {
            return; // Пользователь не забанен
        }
        
        // Восстанавливаем оригинальную роль или устанавливаем роль "User" по умолчанию
        $originalRoleId = $this->original_role_id;
        if (!$originalRoleId) {
            $userRole = Role::where('uniq_name', 'User')->first();
            $originalRoleId = $userRole ? $userRole->role_id : null;
        }
        
        if ($originalRoleId) {
            $this->role_id = $originalRoleId;
            $this->original_role_id = null;
            $this->banned_until = null;
            $this->ban_reason = null; // Очищаем причину бана при разбане
            $this->save();
        }
    }

    public function getBanUntilDate(): ?\Carbon\Carbon
    {
        if (!$this->isBanned()) {
            return null;
        }
        
        if ($this->banned_until) {
            if ($this->banned_until instanceof \Carbon\Carbon) {
                return $this->banned_until->setTimezone('Europe/Moscow');
            }
            return \Carbon\Carbon::parse($this->banned_until, 'Europe/Moscow');
        }
        
        return null; 
    }

    public function isBannedPermanently(): bool
    {
        if (!$this->isBanned()) {
            return false;
        }
        
        return $this->banned_until === null;
    }

}

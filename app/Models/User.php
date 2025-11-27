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
        'need_verification',
        'verification_denied_until',
        'banned_until', // Для временного бана - дата окончания
        'original_role_id', // Сохраняем оригинальную роль перед баном
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts  = [
        'birth_date' => 'date',
        'need_verification' => 'boolean',
        'verification_denied_until' => 'datetime',
        'banned_until' => 'datetime',
        'original_role_id' => 'integer',
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
    
    /**
     * Заказы, где пользователь является заказчиком
     */
    public function ordersAsCustomer(){
        return $this->hasMany(Order::class,"customer_id","user_id");
    }

    /**
     * Проверяет, является ли переданный пользователь владельцем профиля
     * 
     * @param User|null $profileUser Пользователь профиля
     * @return bool
     */
    public function isOwnerOf(?User $profileUser): bool
    {
        if (!$profileUser) {
            return false;
        }

        // Строгое сравнение с приведением типов
        return (int) $this->user_id === (int) $profileUser->user_id;
    }

    /**
     * Проверяет, может ли текущий пользователь просматривать профиль другого пользователя
     * Все могут просматривать профили других пользователей (включая гостей)
     * 
     * @param User|null $profileUser Пользователь профиля
     * @return bool
     */
    public function canViewProfile(?User $profileUser): bool
    {
        if (!$profileUser) {
            return false;
        }

        // Все могут просматривать профили других пользователей
        return true;
    }

    /**
     * Проверяет, может ли текущий пользователь редактировать профиль другого пользователя
     * Только владелец может редактировать свой профиль (администраторы исключены для безопасности)
     * 
     * @param User|null $profileUser Пользователь профиля
     * @return bool
     */
    public function canEditProfile(?User $profileUser): bool
    {
        if (!$profileUser) {
            return false;
        }

        // Только владелец может редактировать свой профиль
        return $this->isOwnerOf($profileUser);
    }

    /**
     * Проверяет, является ли пользователь администратором
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        // Проверяем роль через связь
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles && strtolower($this->roles->uniq_name ?? '') === 'admin';
    }

    /**
     * Проверяет, является ли пользователь арендодателем
     * 
     * @return bool
     */
    public function isRentDealer(): bool
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles && strtolower($this->roles->uniq_name ?? '') === 'rentdealer';
    }

    /**
     * Проверяет, может ли пользователь создавать дома
     * Только арендодатель или администратор могут создавать дома
     * 
     * @return bool
     */
    public function canCreateHouse(): bool
    {
        return $this->isAdmin() || $this->isRentDealer();
    }

    /**
     * Проверяет, является ли пользователь владельцем дома
     * 
     * @param \App\Models\House $house
     * @return bool
     */
    public function isHouseOwner(\App\Models\House $house): bool
    {
        return (int) $this->user_id === (int) $house->user_id;
    }

    /**
     * Проверяет, может ли пользователь редактировать дом
     * Администратор может редактировать любые дома
     * Арендодатель может редактировать только свои дома
     * 
     * @param \App\Models\House $house
     * @return bool
     */
    public function canEditHouse(\App\Models\House $house): bool
    {
        // Администратор может редактировать любые дома
        if ($this->isAdmin()) {
            return true;
        }

        // Арендодатель может редактировать только свои дома
        if ($this->isRentDealer() && $this->isHouseOwner($house)) {
            return true;
        }

        return false;
    }

    /**
     * Проверяет, может ли пользователь удалять дом
     * Администратор может удалять любые дома
     * Арендодатель может удалять только свои дома
     * 
     * @param \App\Models\House $house
     * @return bool
     */
    public function canDeleteHouse(\App\Models\House $house): bool
    {
        // Администратор может удалять любые дома
        if ($this->isAdmin()) {
            return true;
        }

        // Арендодатель может удалять только свои дома
        if ($this->isRentDealer() && $this->isHouseOwner($house)) {
            return true;
        }

        return false;
    }

    /**
     * Проверяет, забанен ли пользователь
     * 
     * @return bool
     */
    public function isBanned(): bool
    {
        // Проверяем, есть ли у пользователя роль "Забанен"
        if (!$this->roles) {
            $this->load('roles');
        }
        
        $bannedRole = Role::where('uniq_name', 'Banned')->first();
        if (!$bannedRole) {
            return false;
        }
        
        // Если роль "Забанен"
        if ($this->role_id == $bannedRole->role_id) {
            // Для временного бана проверяем дату окончания
            if ($this->banned_until) {
                if ($this->banned_until instanceof \Carbon\Carbon) {
                    // Если дата прошла, автоматически разбаниваем
                    if ($this->banned_until->isPast()) {
                        $this->unban();
                        return false;
                    }
                    return true;
                }
                $banDate = \Carbon\Carbon::parse($this->banned_until, 'Europe/Moscow');
                if ($banDate->isPast()) {
                    $this->unban();
                    return false;
                }
                return true;
            }
            // Постоянный бан (нет даты окончания)
            return true;
        }
        
        return false;
    }
    
    /**
     * Разбанивает пользователя, восстанавливая оригинальную роль
     */
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
            $this->save();
        }
    }

    /**
     * Получает дату окончания бана (если есть)
     * 
     * @return \Carbon\Carbon|null
     */
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
        
        return null; // Постоянный бан
    }
    
    /**
     * Проверяет, является ли бан постоянным
     */
    public function isBannedPermanently(): bool
    {
        if (!$this->isBanned()) {
            return false;
        }
        
        return $this->banned_until === null;
    }

}

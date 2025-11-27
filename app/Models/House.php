<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    protected $table = "houses";
    protected $primaryKey = "house_id";
    public $incrementing = true;
    
    protected $fillable = [
        'house_id',
        'user_id',
        'price_id',   
        'rent_type_id',
        'house_type_id',
        'house_calendar_id',
        'adress',
        'area',
        'is_deleted',
        'lng',
        'lat',
        'banned_until',
        'is_banned_permanently',
    ];
    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'area' => 'float',
        'is_deleted' => 'boolean',
        'banned_until' => 'datetime',
        'is_banned_permanently' => 'boolean',
    ];

    protected static function boot(){

        parent::boot();
        static::deleting(function ($house){
            info ("ЕЕЕСТЬ удаление");

        });
    }

    public function user(){
        return $this->belongsTo(User::class,"user_id","user_id");
    }
    public function photo(){
        return $this->hasMany(Photo::class,"house_id","house_id");
    }
    public function order(){
        return $this->hasMany(Order::class,"house_id","house_id");
    }

    public function house_tag(){
        return $this->hasMany(HouseTag::class,"house_id","house_id");
    }
    public function price_list(){
        return $this->hasMany(PriceList::class,"price_list_id","price_list_id");
    }

    public function house_calendar(){
        return $this->hasOne(HouseCalendar::class,"house_id","house_id");
    }

    public function rent_type(){
        return $this->hasOne(RentType::class,"rent_type_id","rent_type_id");
    }

    public function house_type(){
        return $this->hasOne(HouseType::class,"house_type_id","house_type_id");
    }


    
    
    
    public function getRouteKeyName(): string
    {
        return 'house_id';
    }

    /**
     * Scope для фильтрации активных домов (не удаленных и не забаненных)
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('is_deleted')
              ->orWhere('is_deleted', false);
        })
        ->where(function($q) {
            $q->where(function($q2) {
                $q2->whereNull('is_banned_permanently')
                   ->orWhere('is_banned_permanently', false);
            })
            ->where(function($q2) {
                $q2->whereNull('banned_until')
                   ->orWhere('banned_until', '<=', now());
            });
        });
    }

    /**
     * Проверяет, забанен ли дом
     * 
     * @return bool
     */
    public function isBanned(): bool
    {
        if ($this->is_banned_permanently) {
            return true;
        }
        
        if ($this->banned_until) {
            return \Carbon\Carbon::parse($this->banned_until)->isFuture();
        }
        
        return false;
    }

    /**
     * Получает дату окончания бана (если есть)
     * 
     * @return \Carbon\Carbon|null
     */
    public function getBanUntilDate(): ?\Carbon\Carbon
    {
        if ($this->is_banned_permanently) {
            return null; // Постоянный бан
        }
        
        if ($this->banned_until) {
            $date = \Carbon\Carbon::parse($this->banned_until);
            return $date->isFuture() ? $date : null;
        }
        
        return null;
    }

}

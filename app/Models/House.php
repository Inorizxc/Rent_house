<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class House extends Authenticatable
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
        'calendar_id',
        'adress',
        'area',
        'is_deleted',
        'lng',
        'lat',
    ];

    protected static function boot(){

        parent::boot();
        static::deleting(function ($house){
            info ("Нахуй с пляжа");

        });
    }

    public function user(){
        return $this->belongsTo(User::class,"user_id","user_id");
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

    public function order_calendar(){
        return $this->hasMany(OrderCalendar::class,"calendar_id","calendar_id");
    }

    public function rent_type(){
        return $this->hasOne(RentType::class,"rent_type_id","rent_type_id");
    }

    public function house_type(){
        return $this->hasOne(HouseType::class,"house_type_id","house_type_id");
    }
}

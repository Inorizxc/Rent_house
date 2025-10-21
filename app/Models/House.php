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
    protected $primaryKey = "HouseId";
    public $incrementing = true;
    
    protected $fillable = [
        'HouseId',
        'RentDealerId',
        'PriceId',   
        'RentTypeId',
        'HouseTypeId',
        'CalendarId',
        'Adress',
        'Area',
        'Deleted',
        'Ing',
        'Lat',
    ];

    public function user(){
        return $this->belongsTo(User::class,"RentDealerId","UserId");
    }
    
    public function order(){
        return $this->hasMany(Order::class,"HouseId","IdHouse");
    }

    public function tags(){
        return $this->belongsToMany(Tag::class,"HouseId","HouseId");
    }
}

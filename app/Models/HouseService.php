<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class HouseService extends Authenticatable
{
    protected $table = "houseServices";
    protected $primaryKey = "HouseServiceId";
    public $incrementing = true;
    
    protected $fillable = [
        'HouseServiceId',
        'HouseId',
        'ServiceId',
    ];

}

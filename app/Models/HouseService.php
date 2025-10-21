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
    protected $table = "house_services";
    protected $primaryKey = "house_service_id";
    public $incrementing = true;
    
    protected $fillable = [
        'house_service_id',
        'house_id',
        'service_id',
    ];

}

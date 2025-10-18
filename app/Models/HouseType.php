<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class HouseType extends Authenticatable
{
    protected $table = "houseTypes";
    protected $primaryKey = "houseTypeId";
    public $incrementing = true;
    
    protected $fillable = [
        'houseTypeId',
        'Name',
        'Description',
    ];

}

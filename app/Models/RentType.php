<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class RentType extends Authenticatable
{
    protected $table = "rentTypes";
    protected $primaryKey = "rentTypeId";
    public $incrementing = true;
    
    protected $fillable = [
        'RentTypeId',
        'Name',
        'Description',
    ];

}

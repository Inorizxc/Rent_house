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
    protected $table = "rent_types";
    protected $primaryKey = "rent_type_id";
    public $incrementing = true;
    
    protected $fillable = [
        'rent_type_id',
        'name',
        'description',
    ];

}

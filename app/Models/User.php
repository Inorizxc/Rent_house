<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    protected $table = "users";
    protected $primaryKey = "UserId";
    public $incrementing = true;
    
    protected $fillable = [
        'UserId',
        'IdRole',
        'Name',
        'Sename',   
        'Patronimic',
        'BirthDate',
        'Email',
        'Password (cerified)',
        'Phone',
        'Card',
    ];

}

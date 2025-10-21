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
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts  = ['birth_date' => 'date'];


    public function roles(){
        return $this->belongsTo(Role::class,"role_id","role_id");
    }

    public function house(){
        return $this->hasMany(House::class,"user_id","user_id");
    }
    public function order(){
        return $this->hasMany(Order::class,"user_id","user_id");
    }

}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class HouseTag extends Authenticatable
{
    protected $table = "houseTags";
    protected $primaryKey = "HouseTagId";
    public $incrementing = true;
    
    protected $fillable = [
        'HouseTagId',
        'HouseId',
        'TagId',
    ];

    public function house(){
        return $this->belongsToMany(House::class,"HouseId","HouseId");
    }

}

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
    protected $table = "house_types";
    protected $primaryKey = "house_type_id";
    public $incrementing = true;
    
    protected $fillable = [
        'house_type_id',
        'name',
        'description',
    ];

    public function house(){
        return $this->belongsToMany(House::class,"house_type_id","house_type_id");
    }
}

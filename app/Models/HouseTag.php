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
    protected $table = "house_tags";
    protected $primaryKey = "house_tag_id";
    public $incrementing = true;
    
    protected $fillable = [
        'house_tag_id',
        'house_id',
        'tag_id',
    ];

    public function house(){
        return $this->belongsToMany(House::class,"HouseId","HouseId");
    }

}

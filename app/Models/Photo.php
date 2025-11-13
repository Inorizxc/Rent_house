<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;


class Photo extends Model
{
    protected $table = "photos";
    protected $primaryKey = "photo_id";
    public $incrementing = true;
    
    protected $fillable = [
        'photo_id',
        'house_id',
        "user_id",
        'route',
    ];

    public function house(){
        return $this->belongsTo(House::class,"house_id","house_id");
    }
    public function user(){
        return $this->belongsTo(User::class,"user_id","user_id");
    }

}

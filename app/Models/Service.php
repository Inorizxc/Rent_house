<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Model;
class Service extends Model
{
    protected $table = "services";
    protected $primaryKey = "service_id";
    public $incrementing = true;
    
    protected $fillable = [
        'service_id',
        'name',
        'description',
    ];

    public function house_service(){
        return $this->hasMany(HouseService::class,"service_id","service_id");
    }

}

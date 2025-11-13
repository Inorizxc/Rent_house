<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Model;
class OrderStatus extends Model
{
    protected $table = "order_statuses";
    protected $primaryKey = "order_status_id";
    public $incrementing = true;
    
    protected $fillable = [
        'order_status_id',
        'type'
    ];

    public function order(){
        return $this->hasMany(Order::class,"order_status_id","order_status_id");
    }
}

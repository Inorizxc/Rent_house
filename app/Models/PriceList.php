<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Model;
class PriceList extends Model
{
    protected $table = "price_lists";
    protected $primaryKey = "priceList_id";
    public $incrementing = true;
    
    protected $fillable = [
        'price_list_id',
        'price',
    ];

    public function house(){
        return $this->belongsTo(House::class,"price_list_id","price_list_id");
    }
}

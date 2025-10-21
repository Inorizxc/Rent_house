<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "orders";
    protected $primaryKey = "order_id";
    public $incrementing = true;
    
    protected $fillable =
    [
        "order_id",
        "house_id",
        "date_of_order",
        "day_count",
        "customer_id",
        "order_status_id",
        ];
    
    public function house(){
        return $this->hasOne(House::class,"IdHouse","HouseId");
    }

    public function user(){
        return $this->hasOne(User::class,"customer_id","user_id");
    }

    public function order_status(){
        return $this->hasOne(OrderStatus::class,"order_status_id","order_status_id");
    }
}

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
        "order_status",
        ];
    
    public function house(){
        return $this->belongsTo(House::class,"IdHouse","HouseId");
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "orders";
    protected $primaryKey = "IdOrder";
    public $incrementing = true;
    
    protected $fillable =
    [
        "IdOrder",
        "IdHouse",
        "DateOfOrder",
        "DayCount",
        "CustomerId",
        "OrderStatus",
        ];
    
    public function house(){
        return $this->belongsTo(House::class,"IdHouse","HouseId");
    }
}

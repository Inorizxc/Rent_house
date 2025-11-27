<?php

namespace App\Models;

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


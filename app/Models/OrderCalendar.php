<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCalendar extends Model
{
    protected $table = "ordercalendars";
    protected $primaryKey = "IdOrderCalendar";
    public $incrementing = true;
    
    protected $fillable =
    [
        "IdOrderCalendar",
        "IdHouse",
        "OrderDate",
        ];
}

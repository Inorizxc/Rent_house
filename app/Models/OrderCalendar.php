<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCalendar extends Model
{
    protected $table = "order_calendars";
    protected $primaryKey = "order_calendar_id";
    public $incrementing = true;
    
    protected $fillable =
    [
        "order_calendar_id",
        "house_id",
        "order_date",
        ];
}

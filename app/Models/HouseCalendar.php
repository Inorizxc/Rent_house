<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseCalendar extends Model
{
    protected $table = "house_calendar";
    protected $primaryKey = "house_calendar_id";
    public $incrementing = true;
    
    protected $fillable =
    [
        "house_calendar_id",
        "house_id",
        "dates",
        ];
    protected $casts = [
        "dates"=>'array',
    ];
    public function house(){
        return $this->belongsTo(House::class,"house_id","house_id");
    }
}

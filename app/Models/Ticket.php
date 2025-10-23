<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = "tickets";
    protected $primaryKey = "ticket_id";
    public $incrementing = true;
    
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',   
        'is_confirmed',
        "to_check",
    ];

    protected $casts = [
        "is_confirmed"=>"boolean",
        "to_check"=>"boolean"
    ];

    public function user(){
        return $this->belongsTo(User::class,"user_id","user_id");
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Chat extends Model
{
    protected $table = "chats";
    protected $primaryKey = "chat_id";
    public $incrementing = true;
    
    protected $fillable = [
        'chat_id',
        'user_id',
        'rent_dealer_id',
        'user_last_read_at',
        'rent_dealer_last_read_at',
    ];

    protected $casts = [
        'user_last_read_at' => 'datetime',
        'rent_dealer_last_read_at' => 'datetime',
    ];

    public function message(){
        return $this->hasMany(Message::class,'chat_id','chat_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function rentDealer(){
        return $this->belongsTo(User::class, 'rent_dealer_id', 'user_id');
    }
}

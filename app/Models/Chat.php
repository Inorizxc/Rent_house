<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = "chats";
    protected $primaryKey = "chat_id";
    public $incrementing = true;
    
    protected $fillable = [
        'chat_id',
        'user_id',
        'rent_dealer_id',
    ];

    public function message(){
        return $this->hasMany(Message::class,'chat_id','chat_id');
    }
}

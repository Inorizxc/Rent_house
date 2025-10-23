<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = "messages";
    protected $primaryKey = "message_id";
    public $incrementing = true;
    
    protected $fillable = [
        'message_id',
        'chat_id',
        "user_id", //автор
        "message",
    ];

    public function chat(){
        return $this->belongsTo("chats",'chat_id',"chat_id");
    }
}

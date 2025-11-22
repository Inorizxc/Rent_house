<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Chat;
use App\Models\User;

class Message extends Model
{
    protected $table = "messages";
    protected $primaryKey = "message_id";
    public $incrementing = true;
    
    protected $fillable = [
        'chat_id',
        "user_id", //автор
        "message",
    ];

    public function chat(){
        return $this->belongsTo(Chat::class, 'chat_id', 'chat_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

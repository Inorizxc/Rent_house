<?php
namespace App\Services\MessageService;

use App\Models\Chat;
use App\Models\Message;


class MessageService{

    public function getMessages(Chat $chat){
        
        return Message::where('chat_id', $chat->chat_id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();;
    }

}
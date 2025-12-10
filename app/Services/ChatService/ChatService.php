<?php

namespace App\Services\ChatService;

use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
use App\Models\Message;
use App\Models\House;

class ChatService{

    public function getUserChats()
    {
        $currentUser = auth()->user();
        return Chat::where('user_id', $currentUser->user_id)
            ->orWhere('rent_dealer_id', $currentUser->user_id)
            ->with(['user', 'rentDealer'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }  

    public function getChatWithInfo(){
        $chats = ChatService::getUserChats();
        $currentUser = auth()->user();
        return $chats->map(function($chat) use ($currentUser) {
            $interlocutor = ChatService::getInterlocutor($chat);
            $lastMessage = ChatService::getLastMessage($chat);
            $houses = [];
            if ($interlocutor) {
                $houses = House::where('user_id', $interlocutor->user_id)
                    ->where(function($q) {
                        $q->whereNull('is_deleted')
                          ->orWhere('is_deleted', false);
                    })
                    ->with('photo')
                    ->orderBy('house_id', 'desc')
                    ->get();
            }



            return [
                'chat' => $chat,
                'interlocutor' => $interlocutor,
                'lastMessage' => $lastMessage,
                'houses' => $houses,
            ];
        });
    }

    public function getInterlocutor(Chat $chat){
        $chats = ChatService::getUserChats();
        $currentUser = auth()->user();
        return $chat->user_id == $currentUser->user_id 
                ? $chat->rentDealer 
                : $chat->user;;
    }

    public function getLastMessage(Chat $chat){
            return Message::where('chat_id', $chat->chat_id)
                ->with('user')
                ->latest('created_at')
                ->first();
    }
    
    public function getUsersChat($buyerId, $dealerId){

        return Chat::where(function($query) use ($buyerId, $dealerId) {
                $query->where('user_id', $buyerId)
                      ->where('rent_dealer_id', $dealerId);
            })
            ->orWhere(function($query) use ($buyerId, $dealerId) {
                $query->where('user_id', $dealerId)
                      ->where('rent_dealer_id', $buyerId);
            })
            ->first();;
    }
    
    public function update(Chat $chat){
        $currentUser = auth()->user();
        $now = now();
        if ($chat->user_id == $currentUser->user_id) {
            $chat->user_last_read_at = $now;
        } else {
            $chat->rent_dealer_last_read_at = $now;
        }
        $chat->save();
    }

    public function getUnreadCount(){
        $currentUser = auth()->user();
        $chats = ChatService::getUserChats();
        $unreadCount=0;
        foreach ($chats as $chat) {
            $lastMessage = Message::where('chat_id', $chat->chat_id)
                ->latest('created_at')
                ->first();

            if (!$lastMessage) {
                continue;
            }

            $lastReadAt = null;
            if ($chat->user_id == $currentUser->user_id) {
                $lastReadAt = $chat->user_last_read_at;
            } else {
                $lastReadAt = $chat->rent_dealer_last_read_at;
            }

            if ($lastMessage->user_id != $currentUser->user_id) {
                if (!$lastReadAt || $lastMessage->created_at > $lastReadAt) {
                    $unreadCount++;
                }
            }
        }
        return $unreadCount;
    }

}
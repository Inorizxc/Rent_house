<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\TemporaryBlock;
use Illuminate\Http\Request;
use App\Services\ChatService\ChatService;
use App\Services\MessageService\MessageService;


class HouseChatController extends Controller
{   


    public function show($houseId)
    {

        $chatService = app(ChatService::class);
        $messageService = app(MessageService::class);

        $house = House::with(['user', 'photo'])->findOrFail($houseId);
        $currentUser = auth()->user();

        if (!$currentUser) {
            return redirect()->route('login');
        }

        $seller = $house->user;

        if (!$seller) {
            return redirect()->back();
        }

        if ($currentUser->user_id == $seller->user_id) {
            return redirect()->back();
        }

        $buyerId = $currentUser->user_id;
        $dealerId = $seller->user_id;

        $chat = $chatService->getUsersChat($buyerId,$dealerId);

        if (!$chat) {
            $chat = Chat::create([
                'user_id' => $buyerId,
                'rent_dealer_id' => $dealerId,
            ]);
        }

        $chat->load(['user', 'rentDealer']);

        $messages = $messageService->getMessages($chat);

        
        $interlocutor = $chatService->getInterlocutor($chat);

        $chatService->update($chat);

        $house->load('house_calendar');

        TemporaryBlock::cleanExpired();

        $temporaryBlocks = TemporaryBlock::where('house_id', $houseId)
            ->where('expires_at', '>', now())
            ->where('user_id', '!=', $currentUser->user_id)
            ->get();
        
        $temporaryBlockedDates = [];
        foreach ($temporaryBlocks as $block) {
            $temporaryBlockedDates = array_merge($temporaryBlockedDates, $block->dates ?? []);
        }
        $temporaryBlockedDates = array_unique($temporaryBlockedDates);
        $calendar = $house->house_calendar;
        $bookedDates = $calendar ? ($calendar->dates ?? []) : [];
        $allBlockedDates = array_unique(array_merge($bookedDates, $temporaryBlockedDates));

        return view('houses.chat', [
            'house' => $house,
            'chat' => $chat,
            'messages' => $messages,
            'currentUser' => $currentUser,
            'interlocutor' => $interlocutor,
            'blockedDates' => $allBlockedDates,
        ]);
    }

    public function sendMessage(Request $request, $houseId)
    {
        $chatService = app(ChatService::class);
        $messageService = app(MessageService::class);


        $currentUser = auth()->user();

        if (!$currentUser) {
            return redirect()->route('login');
        }

        if ($currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $banReason = $currentUser->ban_reason ? "\n\nПричина: {$currentUser->ban_reason}" : '';
            $message = $currentUser->isBannedPermanently() 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете отправлять сообщения.' . $banReason
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете отправлять сообщения до этой даты." . $banReason;
            
            return response()->json([
                'success' => false,
                'error' => $message
            ], 403);
        }
        
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
        }

        $house = House::findOrFail($houseId);
        $seller = $house->user;

        if (!$seller) {
            return response()->json(['error' => 'Продавец не найден'], 404);
        }

        $buyerId = $currentUser->user_id;
        $dealerId = $seller->user_id;

        $chat = $chatService->getUsersChat($buyerId,$dealerId);

        if (!$chat) {
            $chat = Chat::create([
                'user_id' => $buyerId,
                'rent_dealer_id' => $dealerId,
            ]);
        }

        try {
            $message = Message::create([
                'chat_id' => $chat->chat_id,
                'user_id' => $currentUser->user_id,
                'message' => $validated['message'],
            ]);

            $chat->touch();

            $message->load('user');

            return response()->json([
                'success' => true,
                'message' => [
                    'message_id' => $message->message_id,
                    'chat_id' => $message->chat_id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'created_at' => $message->created_at->toIso8601String(),
                    'user' => [
                        'user_id' => $message->user->user_id,
                        'name' => $message->user->name,
                        'sename' => $message->user->sename,
                        'patronymic' => $message->user->patronymic,
                    ]
                ],
            ]);
        } catch (\Exception $e) {
           
        }
    }

    public function getMessages(Request $request, $houseId)
    {

        $chatService = app(ChatService::class);
        $messageService = app(MessageService::class);


        $currentUser = auth()->user();

        if (!$currentUser) {
            return redirect()->route('login');
        }

        $house = House::findOrFail($houseId);
        $seller = $house->user;

        $buyerId = $currentUser->user_id;
        $dealerId = $seller->user_id;

        $chat = $chatService->getUsersChat($buyerId,$dealerId);

        if (!$chat) {
            return response()->json(['messages' => []]);
        }

        $lastMessageId = $request->query('lastMessageId');

        $query = Message::where('chat_id', $chat->chat_id)
            ->with('user');

        if ($lastMessageId) {
            $query->where('message_id', '>', $lastMessageId);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        return response()->json(['messages' => $messages]);
    }
}


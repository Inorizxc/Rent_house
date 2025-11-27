<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\House;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\ChatService\ChatService;
use App\Services\HouseServices\HouseService;
use App\Services\MessageService\MessageService;

class ChatController extends Controller
{
    /**
     * Display a listing of all chats for the current user.
     */
    public function index()
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }
        $chatService = app(ChatService::class);
        $chatsWithInfo = $chatService->getChatWithInfo();
        return view('chats.index', [
            'chats' => $chatsWithInfo,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       return view('chat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified chat (general chat without house).
     */
    public function show($chatId)
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $chat = Chat::with(['user', 'rentDealer'])->findOrFail($chatId);

        if ($chat->user_id != $currentUser->user_id && $chat->rent_dealer_id != $currentUser->user_id) {
            return redirect()->route('chats.index')->with('error', 'У вас нет доступа к этому чату');
        }
        $chatService = app(ChatService::class);
        $houseService = app(HouseService::class);
        $messageService = app(MessageService::class);

        $interlocutor = $chatService->getInterlocutor($chat);

        if (!$interlocutor) {
            return redirect()->route('chats.index')->with('error', 'Собеседник не найден');
        }

        $messages = $messageService->getMessages($chat);

        $houses = $houseService->getHousesOfUser($interlocutor);

        $house = $houses->count() == 1 ? $houses->first() : null;

        // Обновляем время последнего просмотра чата для текущего пользователя
        $chatService->update($chat);

        return view('chats.show', [
            'chat' => $chat,
            'house' => $house,
            'houses' => $houses,
            'messages' => $messages,
            'currentUser' => $currentUser,
            'interlocutor' => $interlocutor,
        ]);
    }

    /**
     * Create or get chat with a user (from profile page).
     */
    public function startWithUser($userId)
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $otherUser = User::findOrFail($userId);

        if ($currentUser->user_id == $otherUser->user_id) {
            return redirect()->back()->with('error', 'Нельзя начать чат с самим собой');
        }

        $buyerId = $currentUser->user_id;
        $dealerId = $otherUser->user_id;
        
        $chatService = app(ChatService::class);

        $chat = $chatService->getUsersChat($buyerId,$dealerId);

        if (!$chat) {
            $chat = Chat::create([
                'user_id' => $buyerId,
                'rent_dealer_id' => $dealerId,
            ]);
        }

        return redirect()->route('chats.show', $chat->chat_id);
    }

    /**
     * Send message in general chat (without house).
     */
    public function sendMessage(Request $request, $chatId)
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }
        
        // Проверяем, не забанен ли пользователь
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
            return response()->json([
                'success' => false,
                'error' => 'Ошибка валидации: ' . implode(', ', $e->errors()['message'] ?? ['Неверные данные'])
            ], 422);
        }

        $chat = Chat::findOrFail($chatId);

        // Проверяем, что пользователь является участником чата
        if ($chat->user_id != $currentUser->user_id && $chat->rent_dealer_id != $currentUser->user_id) {
            return response()->json([
                'success' => false,
                'error' => 'У вас нет доступа к этому чату'
            ], 403);
        }

        try {
            // Создаем сообщение
            $message = Message::create([
                'chat_id' => $chat->chat_id,
                'user_id' => $currentUser->user_id,
                'message' => $validated['message'],
            ]);

            // Обновляем время обновления чата
            $chat->touch();

            // Загружаем связь с пользователем для ответа
            $message->load('user');

            // Преобразуем сообщение в массив для JSON ответа
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
            \Log::error('Ошибка при отправке сообщения', [
                'chat_id' => $chatId,
                'user_id' => $currentUser->user_id ?? null,
                'message' => $validated['message'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при сохранении сообщения: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get new messages for general chat (without house).
     */
    public function getMessages(Request $request, $chatId)
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return response()->json(['error' => 'Необходима авторизация'], 401);
        }

        $chat = Chat::findOrFail($chatId);

        // Проверяем, что пользователь является участником чата
        if ($chat->user_id != $currentUser->user_id && $chat->rent_dealer_id != $currentUser->user_id) {
            return response()->json(['error' => 'У вас нет доступа к этому чату'], 403);
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat)
    {
        $chat ->delete();
    }

    /**
     * Get unread messages count for current user.
     */
    public function getUnreadCount()
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return response()->json(['unreadCount' => 0], 401);
        }

        $unreadCount = 0;
        $chatService = app(ChatService::class);

        $unreadCount= $chatService->getUnreadCount();
        return response()->json(['unreadCount' => $unreadCount]);
    }
}

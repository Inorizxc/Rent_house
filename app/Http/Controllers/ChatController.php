<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\ChatService\ChatService;
use App\Services\HouseServices\HouseService;
use App\Services\MessageService\MessageService;
use App\Services\AuthService\AuthService;

class ChatController extends Controller
{
    protected $chatService;
    protected $houseService;
    protected $messageService;
    protected $authService;

    public function __construct(
        ChatService $chatService,
        HouseService $houseService,
        MessageService $messageService,
        AuthService $authService
    ) {
        $this->chatService = $chatService;
        $this->houseService = $houseService;
        $this->messageService = $messageService;
        $this->authService = $authService;
    }

    /**
     * Display a listing of all chats for the current user.
     */
    public function index()
    {
        $currentUser = $this->authService->checkAuth();
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }
        
        $chatsWithInfo = $this->chatService->getChatWithInfo();
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
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $chat = Chat::with(['user', 'rentDealer'])->findOrFail($chatId);

        if (!$this->authService->checkChatAccess($currentUser, $chat)) {
            return redirect()->route('chats.index')->with('error', 'У вас нет доступа к этому чату');
        }

        $interlocutor = $this->chatService->getInterlocutor($chat);

        if (!$interlocutor) {
            return redirect()->route('chats.index')->with('error', 'Собеседник не найден');
        }

        $messages = $this->messageService->getMessages($chat);
        $houses = $this->houseService->getHousesOfUser($interlocutor);
        $house = $houses->count() == 1 ? $houses->first() : null;

        // Обновляем время последнего просмотра чата для текущего пользователя
        $this->chatService->update($chat);

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
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $otherUser = User::findOrFail($userId);

        if ($currentUser->user_id == $otherUser->user_id) {
            return redirect()->back()->with('error', 'Нельзя начать чат с самим собой');
        }

        $buyerId = $currentUser->user_id;
        $dealerId = $otherUser->user_id;

        $chat = $this->chatService->getUsersChat($buyerId, $dealerId);

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
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }
        
        // Проверяем, не забанен ли пользователь
        $banCheck = $this->messageService->canSendMessage($currentUser);
        if ($banCheck) {
            return response()->json($banCheck, 403);
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
        if (!$this->authService->checkChatAccess($currentUser, $chat)) {
            return response()->json([
                'success' => false,
                'error' => 'У вас нет доступа к этому чату'
            ], 403);
        }

        try {
            $result = $this->messageService->sendMessage($chat, $currentUser->user_id, $validated['message']);
            return response()->json($result);
        } catch (\Exception $e) {
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
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return response()->json(['error' => 'Необходима авторизация'], 401);
        }

        $chat = Chat::findOrFail($chatId);

        // Проверяем, что пользователь является участником чата
        if (!$this->authService->checkChatAccess($currentUser, $chat)) {
            return response()->json(['error' => 'У вас нет доступа к этому чату'], 403);
        }

        $lastMessageId = $request->query('lastMessageId');
        $messages = $this->messageService->getNewMessages($chat, $lastMessageId);

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
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return response()->json(['unreadCount' => 0], 401);
        }

        $unreadCount = $this->chatService->getUnreadCount();
        return response()->json(['unreadCount' => $unreadCount]);
    }
}

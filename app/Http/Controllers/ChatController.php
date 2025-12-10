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


    public function index()
    {
        $currentUser = $this->authService->checkAuth();
        if (!$currentUser) {
            return redirect()->route('login');
        }
        
        $chatsWithInfo = $this->chatService->getChatWithInfo();
        return view('chats.index', [
            'chats' => $chatsWithInfo,
            'currentUser' => $currentUser,
        ]);
    }

    public function create()
    {
       return view('chat.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($chatId)
    {
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return redirect()->route('login');
        }

        $chat = Chat::with(['user', 'rentDealer'])->findOrFail($chatId);

        if (!$this->authService->checkChatAccess($currentUser, $chat)) {
            return redirect()->route('chats.index');
        }

        $interlocutor = $this->chatService->getInterlocutor($chat);

        if (!$interlocutor) {
            return redirect()->route('chats.index');
        }

        $messages = $this->messageService->getMessages($chat);
        $houses = $this->houseService->getHousesOfUser($interlocutor);
        $house = $houses->count() == 1 ? $houses->first() : null;

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

    public function startWithUser($userId)
    {
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return redirect()->route('login');
        }

        $otherUser = User::findOrFail($userId);

        if ($currentUser->user_id == $otherUser->user_id) {
            return redirect()->back();
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


    public function sendMessage(Request $request, $chatId)
    {
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }

        $banCheck = $this->messageService->canSendMessage($currentUser);
        if ($banCheck) {
            return response()->json($banCheck, 403);
        }
        
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            
        }

        $chat = Chat::findOrFail($chatId);

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
        }
    }

    public function getMessages(Request $request, $chatId)
    {
        $currentUser = $this->authService->checkAuth();

        if (!$currentUser) {
            return response()->json(['error' => 'Необходима авторизация'], 401);
        }

        $chat = Chat::findOrFail($chatId);

        if (!$this->authService->checkChatAccess($currentUser, $chat)) {
            return response()->json(['error' => 'У вас нет доступа к этому чату'], 403);
        }

        $lastMessageId = $request->query('lastMessageId');
        $messages = $this->messageService->getNewMessages($chat, $lastMessageId);

        return response()->json(['messages' => $messages]);
    }

    public function edit(Chat $chat)
    {
        //
    }

    public function update(Request $request, Chat $chat)
    {
        //
    }

    public function destroy(Chat $chat)
    {
        $chat ->delete();
    }

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

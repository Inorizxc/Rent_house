<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\House;
use App\Models\User;
use Illuminate\Http\Request;

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

        // Получаем все чаты, где пользователь является участником
        $chats = Chat::where('user_id', $currentUser->user_id)
            ->orWhere('rent_dealer_id', $currentUser->user_id)
            ->with(['user', 'rentDealer'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Для каждого чата определяем собеседника и последнее сообщение
        $chatsWithInfo = $chats->map(function($chat) use ($currentUser) {
            $interlocutor = $chat->user_id == $currentUser->user_id 
                ? $chat->rentDealer 
                : $chat->user;

            // Получаем последнее сообщение отдельно
            $lastMessage = Message::where('chat_id', $chat->chat_id)
                ->with('user')
                ->latest('created_at')
                ->first();
            
            // Ищем дома собеседника (продавца)
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

        // Проверяем, что пользователь является участником чата
        if ($chat->user_id != $currentUser->user_id && $chat->rent_dealer_id != $currentUser->user_id) {
            return redirect()->route('chats.index')->with('error', 'У вас нет доступа к этому чату');
        }

        // Определяем собеседника
        $interlocutor = $chat->user_id == $currentUser->user_id 
            ? $chat->rentDealer 
            : $chat->user;

        if (!$interlocutor) {
            return redirect()->route('chats.index')->with('error', 'Собеседник не найден');
        }

        // Загружаем сообщения
        $messages = Message::where('chat_id', $chat->chat_id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Получаем дома собеседника (если он продавец)
        $houses = House::where('user_id', $interlocutor->user_id)
            ->where(function($q) {
                $q->whereNull('is_deleted')
                  ->orWhere('is_deleted', false);
            })
            ->with('photo')
            ->orderBy('house_id', 'desc')
            ->get();

        // Если есть только один дом, используем его, иначе null
        $house = $houses->count() == 1 ? $houses->first() : null;

        // Обновляем время последнего просмотра чата для текущего пользователя
        $now = now();
        if ($chat->user_id == $currentUser->user_id) {
            $chat->user_last_read_at = $now;
        } else {
            $chat->rent_dealer_last_read_at = $now;
        }
        $chat->save();

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

        // Не позволяем чатовать с самим собой
        if ($currentUser->user_id == $otherUser->user_id) {
            return redirect()->back()->with('error', 'Нельзя начать чат с самим собой');
        }

        // Определяем, кто покупатель, а кто продавец
        // По умолчанию: текущий пользователь - покупатель (user_id), другой - продавец (rent_dealer_id)
        // Если другой пользователь тоже покупатель (не продавец), то просто используем эту схему
        $buyerId = $currentUser->user_id;
        $dealerId = $otherUser->user_id;

        // Ищем существующий чат или создаем новый
        $chat = Chat::where(function($query) use ($buyerId, $dealerId) {
                $query->where('user_id', $buyerId)
                      ->where('rent_dealer_id', $dealerId);
            })
            ->orWhere(function($query) use ($buyerId, $dealerId) {
                $query->where('user_id', $dealerId)
                      ->where('rent_dealer_id', $buyerId);
            })
            ->first();

        // Если чата нет, создаем новый
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

        $currentUser = auth()->user();

        if (!$currentUser) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
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

        // Получаем все чаты пользователя
        $chats = Chat::where('user_id', $currentUser->user_id)
            ->orWhere('rent_dealer_id', $currentUser->user_id)
            ->get();

        // Подсчитываем чаты с непрочитанными сообщениями
        foreach ($chats as $chat) {
            $lastMessage = Message::where('chat_id', $chat->chat_id)
                ->latest('created_at')
                ->first();

            if (!$lastMessage) {
                continue;
            }

            // Определяем, когда пользователь последний раз просматривал чат
            $lastReadAt = null;
            if ($chat->user_id == $currentUser->user_id) {
                $lastReadAt = $chat->user_last_read_at;
            } else {
                $lastReadAt = $chat->rent_dealer_last_read_at;
            }

            // Если последнее сообщение отправлено не текущим пользователем
            // и оно было создано после последнего просмотра (или чат никогда не просматривался)
            if ($lastMessage->user_id != $currentUser->user_id) {
                if (!$lastReadAt || $lastMessage->created_at > $lastReadAt) {
                    $unreadCount++;
                }
            }
        }

        return response()->json(['unreadCount' => $unreadCount]);
    }
}

<?php
namespace App\Services\MessageService;

use App\Models\Chat;
use App\Models\Message;
use App\Services\AuthService\AuthService;
use Illuminate\Support\Facades\Log;

class MessageService
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function getMessages(Chat $chat)
    {
        return Message::where('chat_id', $chat->chat_id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getNewMessages(Chat $chat, ?int $lastMessageId = null)
    {
        $query = Message::where('chat_id', $chat->chat_id)
            ->with('user');

        if ($lastMessageId) {
            $query->where('message_id', '>', $lastMessageId);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    public function sendMessage(Chat $chat, int $userId, string $messageText): array
    {
        try {
            $message = Message::create([
                'chat_id' => $chat->chat_id,
                'user_id' => $userId,
                'message' => $messageText,
            ]);
            $chat->touch();
            $message->load('user');
            return [
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
            ];
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function canSendMessage($user): ?array
    {
        $banCheck = $this->authService->checkBan($user);
        
        if ($banCheck) {
            return [
                'success' => false,
                'error' => $banCheck['message']
            ];
        }

        return null;
    }
}

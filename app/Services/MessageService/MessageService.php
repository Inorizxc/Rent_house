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

    /**
     * Получает все сообщения чата
     */
    public function getMessages(Chat $chat)
    {
        return Message::where('chat_id', $chat->chat_id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Получает новые сообщения после указанного ID
     */
    public function getNewMessages(Chat $chat, ?int $lastMessageId = null)
    {
        $query = Message::where('chat_id', $chat->chat_id)
            ->with('user');

        if ($lastMessageId) {
            $query->where('message_id', '>', $lastMessageId);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    /**
     * Создает и отправляет сообщение
     */
    public function sendMessage(Chat $chat, int $userId, string $messageText): array
    {
        try {
            // Создаем сообщение
            $message = Message::create([
                'chat_id' => $chat->chat_id,
                'user_id' => $userId,
                'message' => $messageText,
            ]);

            // Обновляем время обновления чата
            $chat->touch();

            // Загружаем связь с пользователем
            $message->load('user');

            // Преобразуем сообщение в массив для JSON ответа
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
            Log::error('Ошибка при отправке сообщения', [
                'chat_id' => $chat->chat_id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Проверяет возможность отправки сообщения (проверка бана)
     */
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

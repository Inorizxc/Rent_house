@extends('layout')

@section('title', 'Мои переписки')

@section('style')
    body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f6f6f7;
    }

    .page-wrapper {
        padding: 10px 24px 24px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: #1f2933;
        margin-bottom: 24px;
    }

    .chats-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .chat-item {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .chat-item:hover {
        border-color: #4f46e5;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        transform: translateY(-2px);
    }

    .chat-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #4f46e5;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .chat-content {
        flex: 1;
        min-width: 0;
    }

    .chat-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
    }

    .chat-name {
        font-size: 16px;
        font-weight: 600;
        color: #1f2933;
        margin: 0;
    }

    .chat-time {
        font-size: 12px;
        color: #6b7280;
        white-space: nowrap;
        margin-left: 12px;
    }

    .chat-last-message {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .chat-houses {
        display: flex;
        gap: 8px;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .house-badge {
        padding: 4px 10px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 12px;
        color: #6b7280;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .house-badge:hover {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }

    .empty-state-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2933;
        margin-bottom: 8px;
    }

    .empty-state-text {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 24px;
    }

    .btn-primary {
        display: inline-block;
        padding: 10px 20px;
        background: #4f46e5;
        color: #ffffff;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s, transform 0.1s;
    }

    .btn-primary:hover {
        background: #4338ca;
        transform: translateY(-1px);
    }

    .chat-unread {
        position: relative;
    }

    .chat-unread::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 12px;
        height: 12px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid #ffffff;
    }
@endsection

@section('main_content')
<div class="page-wrapper">
    <h1 class="page-title">Мои переписки</h1>

    @if($chats->count() > 0)
        <div class="chats-list">
            @foreach($chats as $chatInfo)
                @php
                    $chat = $chatInfo['chat'];
                    $interlocutor = $chatInfo['interlocutor'];
                    $lastMessage = $chatInfo['lastMessage'];
                    $houses = $chatInfo['houses'];

                    // Определяем инициалы для аватара
                    $initials = 'U';
                    if ($interlocutor) {
                        $name = trim(($interlocutor->name ?? '') . ' ' . ($interlocutor->sename ?? ''));
                        if ($name) {
                            $words = explode(' ', $name);
                            $initials = '';
                            foreach ($words as $word) {
                                if (!empty($word)) {
                                    $initials .= mb_substr($word, 0, 1, 'UTF-8');
                                    if (mb_strlen($initials, 'UTF-8') >= 2) break;
                                }
                            }
                            if (empty($initials)) $initials = mb_substr($name, 0, 1, 'UTF-8');
                        }
                        $initials = mb_strtoupper($initials, 'UTF-8');
                    }

                    // Полное имя собеседника
                    $fio = 'Пользователь';
                    if ($interlocutor) {
                        $fio = trim(
                            ($interlocutor->sename ?? '') . ' ' .
                            ($interlocutor->name ?? '') . ' ' .
                            ($interlocutor->patronymic ?? '')
                        );
                        $fio = $fio ?: 'Пользователь #' . $interlocutor->user_id;
                    }

                    // Время последнего сообщения
                    $timeText = 'Нет сообщений';
                    if ($lastMessage && $lastMessage->created_at) {
                        $diff = $lastMessage->created_at->diffForHumans();
                        $timeText = $lastMessage->created_at->format('d.m.Y H:i');
                    } elseif ($chat->updated_at) {
                        $timeText = $chat->updated_at->format('d.m.Y H:i');
                    }
                @endphp

                @if($interlocutor)
                    <div class="chat-item" onclick="window.location.href='{{ route('chats.show', $chat->chat_id) }}'">
                        <div class="chat-avatar">{{ $initials }}</div>
                        <div class="chat-content">
                            <div class="chat-header-row">
                                <h3 class="chat-name">{{ $fio }}</h3>
                                <span class="chat-time">{{ $timeText }}</span>
                            </div>
                            @if($lastMessage)
                                <p class="chat-last-message">
                                    {{ $lastMessage->user_id == $currentUser->user_id ? 'Вы: ' : '' }}
                                    {{ \Illuminate\Support\Str::limit($lastMessage->message, 80) }}
                                </p>
                            @else
                                <p class="chat-last-message" style="color: #9ca3af; font-style: italic;">
                                    Переписка еще не начата
                                </p>
                            @endif
                            @if($houses->count() > 0)
                                <div class="chat-houses">
                                    @foreach($houses as $house)
                                        <a href="{{ route('house.chat', $house->house_id) }}" 
                                           class="house-badge"
                                           onclick="event.stopPropagation();">
                                            Дом #{{ $house->house_id }}
                                            @if($house->adress)
                                                — {{ \Illuminate\Support\Str::limit($house->adress, 30) }}
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">💬</div>
            <h2 class="empty-state-title">У вас пока нет переписок</h2>
            <p class="empty-state-text">
                Начните переписку с продавцом, перейдя на страницу интересующего вас дома
            </p>
            <a href="{{ route('map') }}" class="btn-primary">
                Перейти к карте
            </a>
        </div>
    @endif
</div>
@endsection


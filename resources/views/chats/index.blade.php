@extends('layout')

@section('title', 'Мои переписки')

@section('main_content')
<div class="page-wrapper">


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


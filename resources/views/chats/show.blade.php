@extends('layout')

@section('title', 'Чат')

@section('main_content')
<div class="page-wrapper">
        <div class="chat-container" style="grid-template-columns: {{ $house ? '1fr 3fr' : '1fr' }};">
        @if($house)
        <!-- Панель информации о доме -->
        <div class="house-info-panel">
            <div class="house-info-title">Информация о доме</div>
            
            @if($house->photo && $house->photo->count() > 0)
                <img src="{{ asset('storage/' . $house->photo->first()->path) }}" 
                     alt="{{ $house->photo->first()->name }}" 
                     class="house-photo">
            @endif

            <div class="house-info-item">
                <div class="house-info-label">Адрес</div>
                <div class="house-info-value">{{ $house->adress ?? '—' }}</div>
            </div>

            <div class="house-info-item">
                <div class="house-info-label">Дом #</div>
                <div class="house-info-value">{{ $house->house_id }}</div>
            </div>

            <div class="house-info-item">
                <div class="house-info-label">Площадь</div>
                <div class="house-info-value">
                    {{ $house->area ? $house->area . ' м²' : '—' }}
                </div>
            </div>

            <div class="house-info-item">
                <div class="house-info-label">Стоимость</div>
                <div class="house-info-value">
                    @if($house->price_id)
                        {{ number_format($house->price_id, 0, ',', ' ') }} ₽
                    @else
                        Не указана
                    @endif
                </div>
            </div>

            <a href="{{ route('house.show', $house->house_id) }}" class="house-link">
                Подробнее о доме →
            </a>
        </div>
        @endif

        <!-- Панель чата -->
        <div class="chat-panel">
            <div class="chat-header">
                <div class="chat-header-title">
                    @php
                        $fio = trim(
                            ($interlocutor->sename ?? '') . ' ' .
                            ($interlocutor->name ?? '') . ' ' .
                            ($interlocutor->patronymic ?? '')
                        );
                        $fio = $fio ?: 'Пользователь #' . $interlocutor->user_id;
                    @endphp
                    {{ $fio }}
                </div>
                <div class="chat-header-subtitle">
                    @if($house)
                        Дом: {{ $house->adress ?? 'Адрес не указан' }}
                    @elseif($houses->count() > 0)
                        У собеседника {{ $houses->count() }} {{ $houses->count() == 1 ? 'дом' : 'домов' }}
                    @else
                        Общая переписка
                    @endif
                </div>
                @if($houses->count() > 1)
                    <div style="margin-top: 8px; font-size: 12px; color: #6b7280;">
                        @foreach($houses->take(3) as $h)
                            <a href="{{ route('house.chat', $h->house_id) }}" style="color: #4f46e5; text-decoration: none; margin-right: 8px;">
                                Дом #{{ $h->house_id }}
                            </a>
                        @endforeach
                        @if($houses->count() > 3)
                            <span>...</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="chat-messages" id="chatMessages">
                @if($messages->count() > 0)
                    @foreach($messages as $message)
                        <div class="message {{ $message->user_id == $currentUser->user_id ? 'own' : 'other' }}" data-message-id="{{ $message->message_id }}">
                            @if($message->user_id != $currentUser->user_id)
                                <div class="message-author">
                                    @php
                                        $authorFio = trim(
                                            ($message->user->sename ?? '') . ' ' .
                                            ($message->user->name ?? '') . ' ' .
                                            ($message->user->patronymic ?? '')
                                        );
                                        $authorFio = $authorFio ?: 'Пользователь #' . $message->user->user_id;
                                    @endphp
                                    {{ $authorFio }}
                                </div>
                            @endif
                            <div class="message-content">{!! preg_replace('/Заказ\s*#(\d+)/i', '<a href="/orders/$1" class="order-link" style="color: #4f46e5; text-decoration: underline; font-weight: 600;">Заказ #$1</a>', e($message->message)) !!}</div>
                            <div class="message-meta">
                                {{ $message->created_at->format('d.m.Y H:i') }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-messages">
                        Нет сообщений. Начните переписку!
                    </div>
                @endif
            </div>

            <div class="chat-input-area">
                <form class="chat-input-form" id="chatForm">
                    <textarea 
                        class="chat-input" 
                        id="messageInput" 
                        placeholder="Введите сообщение..." 
                        rows="1"></textarea>
                    <button type="submit" class="btn-send" id="sendButton">
                        Отправить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Конфигурация для модуля чата
    window.chatConfig = {
        chatId: {{ $chat->chat_id }},
        currentUserId: {{ $currentUser->user_id }},
        lastMessageId: {{ $messages->count() > 0 ? $messages->last()->message_id : 0 }}
    };
</script>
@vite(['resources/js/pages/chat.js'])
@endsection


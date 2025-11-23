@extends('layout')

@section('title', 'Чат - Дом #' . $house->house_id)

@section('style')
    body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f6f6f7;
    }

    .page-wrapper {
        padding: 90px 24px 24px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .chat-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 24px;
        height: calc(100vh - 130px);
    }

    .house-info-panel {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
        padding: 18px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        overflow-y: auto;
    }

    .house-info-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 16px;
        color: #1f2933;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 12px;
    }

    .house-info-item {
        margin-bottom: 12px;
    }

    .house-info-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .house-info-value {
        font-size: 14px;
        color: #111827;
        font-weight: 500;
    }

    .house-photo {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 16px;
        border: 1px solid #e2e2e5;
    }

    .chat-panel {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .chat-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .chat-header-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2933;
        margin-bottom: 4px;
    }

    .chat-header-subtitle {
        font-size: 13px;
        color: #6b7280;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f9fafb;
    }

    .message {
        display: flex;
        flex-direction: column;
        max-width: 70%;
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message.own {
        align-self: flex-end;
    }

    .message.other {
        align-self: flex-start;
    }

    .message-content {
        padding: 12px 16px;
        border-radius: 12px;
        word-wrap: break-word;
        font-size: 14px;
        line-height: 1.5;
    }

    .message.own .message-content {
        background: #4f46e5;
        color: #ffffff;
        border-bottom-right-radius: 4px;
    }

    .message.other .message-content {
        background: #ffffff;
        color: #111827;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 4px;
    }

    .message-meta {
        font-size: 11px;
        color: #6b7280;
        margin-top: 4px;
        padding: 0 4px;
    }

    .message-author {
        font-weight: 500;
        margin-bottom: 2px;
    }

    .chat-input-area {
        border-top: 1px solid #e5e7eb;
        padding: 16px 20px;
        background: #ffffff;
    }

    .chat-input-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .chat-input {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        resize: none;
        min-height: 44px;
        max-height: 120px;
        line-height: 1.5;
    }

    .chat-input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .btn-send {
        padding: 10px 20px;
        background: #4f46e5;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        white-space: nowrap;
    }

    .btn-send:hover:not(:disabled) {
        background: #4338ca;
        transform: translateY(-1px);
    }

    .btn-send:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .payment-section {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 2px solid #e5e7eb;
    }

    .payment-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 16px;
        color: #1f2933;
    }

    .date-selector {
        margin-bottom: 16px;
    }

    .date-input-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    .date-input {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        width: 100%;
    }

    .date-input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .btn-pay {
        width: 100%;
        padding: 12px;
        background: #10b981;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
    }

    .btn-pay:hover {
        background: #059669;
        transform: translateY(-1px);
    }

    .empty-messages {
        text-align: center;
        color: #9ca3af;
        font-size: 14px;
        margin-top: 40px;
    }

    @media (max-width: 900px) {
        .chat-container {
            grid-template-columns: 1fr;
            height: auto;
            min-height: calc(100vh - 130px);
        }

        .house-info-panel {
            order: 2;
            max-height: 400px;
        }

        .chat-panel {
            order: 1;
            min-height: 600px;
        }
    }
@endsection

@section('main_content')
<div class="page-wrapper">
    <div class="chat-container">
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

            <div class="house-info-item">
                <div class="house-info-label">Продавец</div>
                <div class="house-info-value">
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
            </div>

            <!-- Секция оплаты -->
            <div class="payment-section">
                <div class="payment-title">Оплата аренды</div>
                
                <div class="date-selector">
                    <div class="date-input-group">
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 6px;">
                                Дата заезда
                            </label>
                            <input type="date" 
                                   id="checkinDate" 
                                   class="date-input" 
                                   name="checkin_date"
                                   min="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 6px;">
                                Дата выезда
                            </label>
                            <input type="date" 
                                   id="checkoutDate" 
                                   class="date-input" 
                                   name="checkout_date"
                                   min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <button type="button" 
                        class="btn-pay" 
                        onclick="handlePayment()"
                        id="payButton">
                    Оплатить аренду
                </button>
                <div id="paymentMessage" style="margin-top: 8px; font-size: 12px; color: #6b7280; text-align: center;">
                    Функция пока не реализована
                </div>
            </div>
        </div>

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
                <div class="chat-header-subtitle">Дом: {{ $house->adress ?? 'Адрес не указан' }}</div>
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
                            <div class="message-content">{{ $message->message }}</div>
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
                <form class="chat-input-form" id="chatForm" onsubmit="sendMessage(event)">
                    <textarea 
                        class="chat-input" 
                        id="messageInput" 
                        placeholder="Введите сообщение..." 
                        rows="1"
                        oninput="autoResize(this)"
                        onkeydown="handleKeyDown(event)"></textarea>
                    <button type="submit" class="btn-send" id="sendButton">
                        Отправить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const houseId = {{ $house->house_id }};
    const currentUserId = {{ $currentUser->user_id }};
    let lastMessageId = {{ $messages->count() > 0 ? $messages->last()->message_id : 0 }};

    // Автоизменение размера textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    // Обработка нажатия клавиш (Enter для отправки, Shift+Enter для новой строки)
    function handleKeyDown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            const form = document.getElementById('chatForm');
            if (form) {
                sendMessage(event);
            }
        }
    }

    // Функция обработки оплаты (пока не реализована)
    function handlePayment() {
        const checkinDate = document.getElementById('checkinDate').value;
        const checkoutDate = document.getElementById('checkoutDate').value;
        
        if (!checkinDate || !checkoutDate) {
            alert('Пожалуйста, выберите даты заезда и выезда');
            return;
        }

        if (new Date(checkoutDate) <= new Date(checkinDate)) {
            alert('Дата выезда должна быть позже даты заезда');
            return;
        }

        alert('Функция оплаты пока не реализована.\nВыбранные даты:\nЗаезд: ' + checkinDate + '\nВыезд: ' + checkoutDate);
    }

    // Отправка сообщения
    function sendMessage(event) {
        event.preventDefault();
        
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        
        if (!message) {
            return;
        }

        const sendButton = document.getElementById('sendButton');
        sendButton.disabled = true;
        sendButton.textContent = 'Отправка...';

        fetch(`/house/${houseId}/chat/message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: message
            })
        })
        .then(response => {
            // Проверяем статус ответа
            if (!response.ok) {
                // Если ответ не успешный, пытаемся получить JSON с ошибкой
                return response.json().then(err => {
                    throw new Error(err.error || err.message || 'Ошибка при отправке сообщения');
                }).catch(() => {
                    throw new Error(`Ошибка ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            // Проверяем успешность операции
            if (data.success && data.message) {
                messageInput.value = '';
                messageInput.style.height = 'auto';
                
                // Обновляем lastMessageId
                lastMessageId = data.message.message_id;
                
                // Добавляем новое сообщение в чат
                addMessageToChat(data.message, true);
                
                // Прокручиваем вниз
                scrollToBottom();
            } else {
                // Если нет success или message, это ошибка
                throw new Error(data.error || 'Неизвестная ошибка при отправке сообщения');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Ошибка при отправке сообщения');
        })
        .finally(() => {
            sendButton.disabled = false;
            sendButton.textContent = 'Отправить';
        });
    }

    // Добавление сообщения в чат
    function addMessageToChat(messageData, isOwn) {
        const messagesContainer = document.getElementById('chatMessages');
        
        // Проверяем, не добавлено ли уже это сообщение
        const existingMessage = messagesContainer.querySelector(`[data-message-id="${messageData.message_id}"]`);
        if (existingMessage) {
            return; // Сообщение уже существует
        }
        
        // Удаляем сообщение "Нет сообщений", если оно есть
        const emptyMessages = messagesContainer.querySelector('.empty-messages');
        if (emptyMessages) {
            emptyMessages.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isOwn ? 'own' : 'other'}`;
        messageDiv.setAttribute('data-message-id', messageData.message_id);
        
        const authorFio = messageData.user ? 
            `${messageData.user.sename || ''} ${messageData.user.name || ''} ${messageData.user.patronymic || ''}`.trim() || 
            `Пользователь #${messageData.user.user_id}` : 
            'Пользователь';

        if (!isOwn) {
            const authorDiv = document.createElement('div');
            authorDiv.className = 'message-author';
            authorDiv.textContent = authorFio;
            messageDiv.appendChild(authorDiv);
        }

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = messageData.message;
        messageDiv.appendChild(contentDiv);

        const metaDiv = document.createElement('div');
        metaDiv.className = 'message-meta';
        const date = new Date(messageData.created_at);
        metaDiv.textContent = date.toLocaleDateString('ru-RU') + ' ' + 
                             date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
        messageDiv.appendChild(metaDiv);

        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }

    // Прокрутка вниз
    function scrollToBottom() {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Загрузка новых сообщений (периодически или по событию)
    let isPolling = true;
    let pollingInterval = null;
    
    function loadNewMessages() {
        if (!isPolling) return;
        
        fetch(`/house/${houseId}/chat/messages?lastMessageId=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    const messagesContainer = document.getElementById('chatMessages');
                    const wasScrolledToBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop <= messagesContainer.clientHeight + 100;
                    
                    let hasNewMessages = false;
                    data.messages.forEach(msg => {
                        if (msg.message_id > lastMessageId) {
                            // Проверяем, что сообщение еще не добавлено
                            const existingMessage = document.querySelector(`[data-message-id="${msg.message_id}"]`);
                            if (!existingMessage) {
                                addMessageToChat(msg, msg.user_id == currentUserId);
                                lastMessageId = msg.message_id;
                                hasNewMessages = true;
                            }
                        }
                    });
                    
                    // Прокручиваем вниз только если пользователь был внизу или это новое сообщение
                    if (hasNewMessages && (wasScrolledToBottom || data.messages.length > 0)) {
                        scrollToBottom();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                // Останавливаем polling при ошибке, чтобы не спамить
                // setInterval продолжит работать, но функция вернется раньше
            });
    }

    // Прокручиваем вниз при загрузке страницы
    window.addEventListener('load', () => {
        scrollToBottom();
        
        // Запускаем периодическую проверку новых сообщений каждые 2 секунды
        pollingInterval = setInterval(loadNewMessages, 2000);
    });

    // Останавливаем polling при уходе со страницы
    window.addEventListener('beforeunload', () => {
        isPolling = false;
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });

    // Останавливаем polling когда вкладка неактивна
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            isPolling = false;
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        } else {
            isPolling = true;
            pollingInterval = setInterval(loadNewMessages, 2000);
            // Сразу проверяем новые сообщения при возврате
            loadNewMessages();
        }
    });
</script>
@endsection


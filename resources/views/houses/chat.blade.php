@extends('layout')

@section('title', 'Чат - Дом #' . $house->house_id)

@section('main_content')
<div class="page-wrapper">
    <div class="chat-container">
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

            <div class="payment-section">
                <div class="payment-title">Оплата аренды</div>
                
                <div class="date-selector">
                    <div class="house-calendar-container" 
                         data-house-id="{{ $house->house_id }}" 
                         data-dates='@json($blockedDates ?? $house->house_calendar->dates ?? [])'
                         data-readonly="true">
                        <div class="calendar-wrapper">
                            <div class="calendar-header">
                                <button class="calendar-nav-btn" data-action="prev">‹</button>
                                <div class="calendar-month-year"></div>
                                <button class="calendar-nav-btn" data-action="next">›</button>
                            </div>
                            <div class="calendar-grid">
                                <div class="calendar-weekdays">
                                    <div>Пн</div>
                                    <div>Вт</div>
                                    <div>Ср</div>
                                    <div>Чт</div>
                                    <div>Пт</div>
                                    <div>Сб</div>
                                    <div>Вс</div>
                                </div>
                                <div class="calendar-days"></div>
                            </div>
                        </div>
                    </div>
                    <div id="selectedDatesInfo" style="margin-top: 12px; font-size: 13px; color: #6b7280; text-align: center;">
                        Выберите период аренды
                    </div>
                </div>

                <button type="button" 
                        class="btn-pay" 
                        onclick="handlePayment()"
                        id="payButton"
                        disabled>
                    Оплатить аренду
                </button>
                <div id="paymentMessage" style="margin-top: 8px; font-size: 12px; color: #6b7280; text-align: center;">
                </div>
            </div>
        </div>

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

    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    function handleKeyDown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            const form = document.getElementById('chatForm');
            if (form) {
                sendMessage(event);
            }
        }
    }

    let selectedCheckinDate = null;
    let selectedCheckoutDate = null;
    let selectedDates = [];
    let isRangeSelecting = false;
    let rangeStartDate = null;
    
    let isDragging = false;
    let dragStartDate = null;
    let dragEndDate = null;
    let wasDragging = false;
    let isDragRemoving = false;
    let draggedDates = new Set();

    function showBanNotification(message) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(220, 38, 38, 0.4);
            z-index: 10000;
            max-width: 400px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            animation: slideInRight 0.3s ease-out;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <svg style="width: 24px; height: 24px; flex-shrink: 0; margin-top: 2px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                </svg>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 4px; font-size: 15px;">Ваш аккаунт заблокирован</div>
                    <div style="opacity: 0.95; font-size: 13px;">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: #ffffff; cursor: pointer; font-size: 20px; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">×</button>
            </div>
        `;
        
        if (!document.getElementById('ban-notification-styles')) {
            const style = document.createElement('style');
            style.id = 'ban-notification-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideInRight 0.3s ease-out reverse';
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 8000);
    }

    async function handlePayment() {
        if (!selectedCheckinDate || !selectedCheckoutDate) {
            alert('Пожалуйста, выберите период аренды');
            return;
        }

        const payButton = document.getElementById('payButton');
        const paymentMessage = document.getElementById('paymentMessage');
        
        payButton.disabled = true;
        payButton.textContent = 'Обработка...';
        paymentMessage.textContent = '';
        paymentMessage.style.color = '#6b7280';

        try {
            const response = await fetch(`/house/${houseId}/order`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    checkin_date: selectedCheckinDate,
                    checkout_date: selectedCheckoutDate
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    const params = new URLSearchParams({
                        checkin_date: selectedCheckinDate,
                        checkout_date: selectedCheckoutDate
                    });
                    window.location.href = `/house/${houseId}/order/confirm?${params.toString()}`;
                }
            } else {
                const errorMessage = data.error || 'Ошибка при создании заказа';
                if (response.status === 403 || errorMessage.includes('заблокирован')) {
                    showBanNotification(errorMessage);
                    throw new Error(errorMessage);
                } else {
                    throw new Error(errorMessage);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            const errorMessage = error.message || 'Ошибка при создании заказа';
            paymentMessage.textContent = errorMessage;
            paymentMessage.style.color = '#ef4444';
            paymentMessage.style.fontWeight = '500';
            
            showBanNotification(errorMessage);
        } finally {
            payButton.disabled = false;
            payButton.textContent = 'Оплатить аренду';
        }
    }

    function updateSelectedDatesInfo() {
        const infoEl = document.getElementById('selectedDatesInfo');
        if (selectedCheckinDate && selectedCheckoutDate) {
            const checkin = new Date(selectedCheckinDate);
            const checkout = new Date(selectedCheckoutDate);
            const days = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
            infoEl.textContent = `Заезд: ${checkin.toLocaleDateString('ru-RU')} - Выезд: ${checkout.toLocaleDateString('ru-RU')} (${days} ${days === 1 ? 'день' : days < 5 ? 'дня' : 'дней'})`;
            infoEl.style.color = '#111827';
            infoEl.style.fontWeight = '500';
        } else if (isRangeSelecting && rangeStartDate) {
            infoEl.textContent = `Выбрана начальная дата: ${new Date(rangeStartDate).toLocaleDateString('ru-RU')}. Выберите конечную дату (Ctrl+клик)`;
            infoEl.style.color = '#3b82f6';
            infoEl.style.fontWeight = '400';
        } else if (selectedDates.length > 0) {
            infoEl.textContent = `Выбрано дат: ${selectedDates.length}. Выберите непрерывный период.`;
            infoEl.style.color = '#f59e0b';
            infoEl.style.fontWeight = '400';
        } else {
            infoEl.textContent = 'Выберите период аренды (Выберите дату и, не отпуская левую кнопку мыши, наведитеcь на те даты, которые хотите выбрать.)';
            infoEl.style.color = '#6b7280';
            infoEl.style.fontWeight = '400';
        }
    }

    function updatePayButton() {
        const payButton = document.getElementById('payButton');
        payButton.disabled = !(selectedCheckinDate && selectedCheckoutDate);
    }

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
            if (!response.ok) {
                return response.json().then(err => {
                    const errorMessage = err.error || err.message || 'Ошибка при отправке сообщения';
                    if (response.status === 403 || errorMessage.includes('заблокирован')) {
                        showBanNotification(errorMessage);
                    }
                    throw new Error(errorMessage);
                }).catch(() => {
                    throw new Error(`Ошибка ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.message) {
                messageInput.value = '';
                messageInput.style.height = 'auto';
                
                lastMessageId = data.message.message_id;
                
                addMessageToChat(data.message, true);
                
                scrollToBottom();
            } else {
                const errorMessage = data.error || 'Неизвестная ошибка при отправке сообщения';
                if (errorMessage.includes('заблокирован')) {
                    showBanNotification(errorMessage);
                }
                throw new Error(errorMessage);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMessage = error.message || 'Ошибка при отправке сообщения';
            if (errorMessage.includes('заблокирован')) {
                showBanNotification(errorMessage);
            } else {
                alert(errorMessage);
            }
        })
        .finally(() => {
            sendButton.disabled = false;
            sendButton.textContent = 'Отправить';
        });
    }

    function addMessageToChat(messageData, isOwn) {
        const messagesContainer = document.getElementById('chatMessages');
        
        const existingMessage = messagesContainer.querySelector(`[data-message-id="${messageData.message_id}"]`);
        if (existingMessage) {
            return;
        }
        
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
        contentDiv.innerHTML = processOrderLinks(messageData.message);
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

    function processOrderLinks(text) {
        return text.replace(/Заказ\s*#(\d+)/gi, (match, orderId) => {
            return `<a href="/orders/${orderId}" class="order-link" style="color: #4f46e5; text-decoration: underline; font-weight: 600;">Заказ #${orderId}</a>`;
        });
    }

    function scrollToBottom() {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

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
                            const existingMessage = document.querySelector(`[data-message-id="${msg.message_id}"]`);
                            if (!existingMessage) {
                                addMessageToChat(msg, msg.user_id == currentUserId);
                                lastMessageId = msg.message_id;
                                hasNewMessages = true;
                            }
                        }
                    });
                    
                    if (hasNewMessages && (wasScrolledToBottom || data.messages.length > 0)) {
                        scrollToBottom();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    window.addEventListener('load', () => {
        scrollToBottom();
        
        pollingInterval = setInterval(loadNewMessages, 2000);
    });

    window.addEventListener('beforeunload', () => {
        isPolling = false;
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            isPolling = false;
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        } else {
            isPolling = true;
            pollingInterval = setInterval(loadNewMessages, 2000);
            loadNewMessages();
        }
    });

    (function() {
        function initChatCalendar() {
            const container = document.querySelector('.house-calendar-container[data-readonly="true"]');
            if (!container) return;

            const houseId = container.dataset.houseId;
            const datesData = container.dataset.dates;
            let bookedDates = [];
            
            try {
                bookedDates = datesData ? JSON.parse(datesData) : [];
                bookedDates = bookedDates.map(date => {
                    if (typeof date === 'string') {
                        return date.split('T')[0];
                    }
                    return date;
                });
            } catch (e) {
                console.warn('Ошибка парсинга дат календаря:', e);
            }

            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();

            function getDatesInRange(startDate, endDate) {
                const dates = [];
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (start > end) {
                    [start, end] = [end, start];
                }
                
                const current = new Date(start);
                while (current <= end) {
                    dates.push(current.toISOString().split('T')[0]);
                    current.setDate(current.getDate() + 1);
                }
                
                return dates;
            }

            function validateAndNormalizeSelectedDates() {
                if (!selectedDates || selectedDates.length === 0) {
                    selectedCheckinDate = null;
                    selectedCheckoutDate = null;
                    return { valid: true };
                }

                selectedDates.sort();
                
                const minDate = selectedDates[0];
                const maxDate = selectedDates[selectedDates.length - 1];
                
                const allDatesInRange = getDatesInRange(minDate, maxDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                for (let i = 0; i < allDatesInRange.length; i++) {
                    const dateStr = allDatesInRange[i];
                    const date = new Date(dateStr);
                    date.setHours(0, 0, 0, 0);
                    
                    if (date < today) continue;
                    
                    if (bookedDates.includes(dateStr)) {
                        return {
                            valid: false,
                            error: 'Выбранный период содержит занятые даты'
                        };
                    }
                    
                    if (!selectedDates.includes(dateStr)) {
                        return {
                            valid: false,
                            error: 'Выбранный период содержит пропуски. Выберите непрерывный период.'
                        };
                    }
                }
                
                selectedCheckinDate = minDate;
                const checkoutDate = new Date(maxDate);
                checkoutDate.setDate(checkoutDate.getDate() + 1);
                selectedCheckoutDate = checkoutDate.toISOString().split('T')[0];
                
                return { valid: true };
            }

   

            function selectDateRange(startDate, endDate) {
                const rangeDates = getDatesInRange(startDate, endDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                const validDates = rangeDates.filter(dateStr => {
                    const date = new Date(dateStr);
                    date.setHours(0, 0, 0, 0);
                    return date >= today && !bookedDates.includes(dateStr);
                });

                validDates.forEach(dateStr => {
                    if (!selectedDates.includes(dateStr)) {
                        selectedDates.push(dateStr);
                    }
                });
                
                const validation = validateAndNormalizeSelectedDates();
                
                if (!validation.valid) {
                    selectedDates = [];
                    selectedCheckinDate = null;
                    selectedCheckoutDate = null;
                    if (validation.error) {
                        alert(validation.error);
                    }
                }
                
                renderCalendar();
                updateSelectedDatesInfo();
                updatePayButton();
            }

            function toggleDate(dateStr, isRangeMode = false) {
                if (isRangeMode) {
                    if (!rangeStartDate) {
                        rangeStartDate = dateStr;
                        isRangeSelecting = true;
                        renderCalendar();
                        return;
                    } else if (rangeStartDate === dateStr) {
                        rangeStartDate = null;
                        isRangeSelecting = false;
                        renderCalendar();
                        return;
                    } else {
                        const startDate = new Date(rangeStartDate);
                        const endDate = new Date(dateStr);
                        const actualStart = startDate <= endDate ? rangeStartDate : dateStr;
                        const actualEnd = startDate <= endDate ? dateStr : rangeStartDate;
                        
                        selectDateRange(actualStart, actualEnd);
                        
                        rangeStartDate = null;
                        isRangeSelecting = false;
                        return;
                    }
                }

                const index = selectedDates.indexOf(dateStr);
                if (index > -1) {
                    selectedDates.splice(index, 1);
                } else {
                    if (selectedDates.length === 1) {
                        const existingDate = selectedDates[0];
                        const startDate = new Date(existingDate);
                        const endDate = new Date(dateStr);
                        const actualStart = startDate <= endDate ? existingDate : dateStr;
                        const actualEnd = startDate <= endDate ? dateStr : existingDate;
                        
                        selectedDates = [];
                        selectDateRange(actualStart, actualEnd);
                        return;
                    } else {
                        selectedDates.push(dateStr);
                    }
                }
                
                const validation = validateAndNormalizeSelectedDates();
                
                if (!validation.valid) {
                    selectedDates = [];
                    selectedCheckinDate = null;
                    selectedCheckoutDate = null;
                    if (validation.error) {
                        alert(validation.error);
                    }
                }
                
                renderCalendar();
                updateSelectedDatesInfo();
                updatePayButton();
            }

            const renderCalendar = function() {
                const monthYearEl = container.querySelector('.calendar-month-year');
                const daysEl = container.querySelector('.calendar-days');
                
                const monthNames = [
                    'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                    'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
                ];

                monthYearEl.textContent = `${monthNames[currentMonth]} ${currentYear}`;

                const firstDay = new Date(currentYear, currentMonth, 1);
                const lastDay = new Date(currentYear, currentMonth + 1, 0);
                const daysInMonth = lastDay.getDate();
                const startingDayOfWeek = (firstDay.getDay() + 6) % 7;

                daysEl.innerHTML = '';

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const prevMonthLastDay = new Date(currentYear, currentMonth, 0).getDate();
                for (let i = startingDayOfWeek - 1; i >= 0; i--) {
                    const day = prevMonthLastDay - i;
                    const dayEl = document.createElement('div');
                    dayEl.className = 'calendar-day other-month';
                    dayEl.textContent = day;
                    daysEl.appendChild(dayEl);
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const dayEl = document.createElement('div');
                    const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const currentDayDate = new Date(currentYear, currentMonth, day);
                    currentDayDate.setHours(0, 0, 0, 0);
                    
                    dayEl.className = 'calendar-day';
                    dayEl.textContent = day;
                    dayEl.dataset.date = dateStr;

                    if (currentDayDate < today) {
                        dayEl.classList.add('past-date');
                    }

                    if (currentYear === today.getFullYear() && 
                        currentMonth === today.getMonth() && 
                        day === today.getDate()) {
                        dayEl.classList.add('today');
                    }

                    if (bookedDates.includes(dateStr)) {
                        dayEl.classList.add('booked');
                        dayEl.title = 'Дата занята';
                    } else if (currentDayDate >= today) {
                        if (selectedDates.includes(dateStr)) {
                            dayEl.classList.add('selected');
                        }
                        
                        if (isRangeSelecting && rangeStartDate === dateStr) {
                            dayEl.classList.add('range-start');
                        }
                        
                        dayEl.addEventListener('mousedown', function(e) {
                            if (e.button === 0 && !bookedDates.includes(dateStr)) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                isDragging = true;
                                dragStartDate = dateStr;
                                dragEndDate = dateStr;
                                
                                isDragRemoving = selectedDates.includes(dateStr);
                                
                                draggedDates = new Set();
                                draggedDates.add(dateStr);
                                
                                dayEl.classList.add('dragging');
                                
                                document.body.style.cursor = isDragRemoving ? 'not-allowed' : 'grabbing';
                                
                                document.addEventListener('mousemove', handleMouseMove);
                                document.addEventListener('mouseup', handleMouseUp);
                            }
                        });
                        
                        dayEl.addEventListener('mouseenter', function() {
                            if (isDragging && !bookedDates.includes(dateStr) && currentDayDate >= today) {
                                const wasAdded = draggedDates.has(dateStr);
                                if (!wasAdded) {
                                    draggedDates.add(dateStr);
                                    dragEndDate = dateStr;
                                    renderCalendar();
                                }
                            }
                        });
                        
                        dayEl.addEventListener('click', function(e) {
                            if (wasDragging) {
                                wasDragging = false;
                                return;
                            }
                            
                            const isCtrlPressed = e.ctrlKey || e.metaKey;
                            toggleDate(dateStr, isCtrlPressed);
                        });
                        
                        if (isRangeSelecting) {
                            if (rangeStartDate === dateStr) {
                                dayEl.title = 'Начало промежутка (нажмите Ctrl+клик на конечную дату)';
                            } else {
                                dayEl.title = 'Ctrl+клик для выбора конечной даты промежутка';
                            }
                        } else if (selectedDates.includes(dateStr)) {
                            dayEl.title = 'Нажмите, чтобы убрать из выбора. Зажмите ЛКМ и перетащите для выбора промежутка';
                        } else {
                            dayEl.title = 'Клик для выбора. Зажмите ЛКМ и перетащите для выбора промежутка';
                        }
                    }

                    if (selectedCheckinDate && selectedCheckoutDate) {
                        const rangeDates = getDatesInRange(selectedCheckinDate, selectedCheckoutDate);
                        rangeDates.pop();
                        
                        if (rangeDates.includes(dateStr)) {
                            dayEl.classList.remove('selected');
                            if (dateStr === selectedCheckinDate) {
                                dayEl.classList.add('range-start');
                            } else if (dateStr === rangeDates[rangeDates.length - 1]) {
                                dayEl.classList.add('range-end');
                            } else {
                                dayEl.classList.add('range-middle');
                            }
                        }
                    } else if (selectedDates.includes(dateStr)) {
                        dayEl.classList.add('selected');
                    }
                    
                    if (isRangeSelecting && rangeStartDate) {
                        const startDate = new Date(rangeStartDate);
                        const currentDate = new Date(dateStr);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        
                        if (currentDate >= today && !bookedDates.includes(dateStr)) {
                            if (dateStr === rangeStartDate) {
                                dayEl.classList.add('range-start');
                            } else if (startDate < currentDate) {
                                const previewRange = getDatesInRange(rangeStartDate, dateStr);
                                if (previewRange.includes(dateStr) && dateStr !== rangeStartDate) {
                                    if (dateStr === previewRange[previewRange.length - 1]) {
                                        dayEl.classList.add('range-end');
                                    } else {
                                        dayEl.classList.add('range-middle');
                                    }
                                }
                            }
                        }
                    }
                    
                    if (isDragging && draggedDates && draggedDates.has(dateStr)) {
                        const currentDate = new Date(dateStr);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        
                        if (currentDate >= today && !bookedDates.includes(dateStr)) {
                            if (!isDragRemoving) {
                                const datesArray = Array.from(draggedDates).sort();
                                if (datesArray.length > 0) {
                                    if (dateStr === datesArray[0]) {
                                        dayEl.classList.add('range-start');
                                    } else if (dateStr === datesArray[datesArray.length - 1]) {
                                        dayEl.classList.add('range-end');
                                    } else {
                                        dayEl.classList.add('range-middle');
                                    }
                                }
                            }
                        }
                    }

                    daysEl.appendChild(dayEl);
                }

                const totalCells = startingDayOfWeek + daysInMonth;
                const remainingCells = 42 - totalCells;
                for (let day = 1; day <= remainingCells && day <= 14; day++) {
                    const dayEl = document.createElement('div');
                    dayEl.className = 'calendar-day other-month';
                    dayEl.textContent = day;
                    daysEl.appendChild(dayEl);
                }
            }

            container.querySelectorAll('.calendar-nav-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.dataset.action === 'prev') {
                        currentMonth--;
                        if (currentMonth < 0) {
                            currentMonth = 11;
                            currentYear--;
                        }
                    } else {
                        currentMonth++;
                        if (currentMonth > 11) {
                            currentMonth = 0;
                            currentYear++;
                        }
                    }
                    isRangeSelecting = false;
                    rangeStartDate = null;
                    renderCalendar();
                });
            });

            function handleMouseMove(e) {
                if (!isDragging) return;
                
                const elementUnderMouse = document.elementFromPoint(e.clientX, e.clientY);
                if (!elementUnderMouse) return;
                
                let dayElement = elementUnderMouse;
                while (dayElement && !dayElement.dataset.date) {
                    dayElement = dayElement.parentElement;
                }
                
                if (dayElement && dayElement.dataset.date) {
                    const newDate = dayElement.dataset.date;
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const date = new Date(newDate);
                    date.setHours(0, 0, 0, 0);
                    
                    if (date >= today && !bookedDates.includes(newDate)) {
                        const wasAdded = draggedDates.has(newDate);
                        if (!wasAdded) {
                            draggedDates.add(newDate);
                            dragEndDate = newDate;
                            renderCalendar();
                        }
                    }
                }
            }
            
            function handleMouseUp(e) {
                if (!isDragging) return;
                
                wasDragging = true;
                
                document.body.style.cursor = '';
                
                document.removeEventListener('mousemove', handleMouseMove);
                document.removeEventListener('mouseup', handleMouseUp);
                
                container.querySelectorAll('.calendar-day.dragging').forEach(el => {
                    el.classList.remove('dragging');
                });
                
                if (draggedDates.size > 0) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    const validDates = Array.from(draggedDates).filter(dateStr => {
                        const date = new Date(dateStr);
                        date.setHours(0, 0, 0, 0);
                        return date >= today && !bookedDates.includes(dateStr);
                    });
                    
                    if (validDates.length > 0) {
                        if (isDragRemoving) {
                            validDates.forEach(dateStr => {
                                const index = selectedDates.indexOf(dateStr);
                                if (index > -1) {
                                    selectedDates.splice(index, 1);
                                }
                            });
                        } else {
                            validDates.forEach(dateStr => {
                                if (!selectedDates.includes(dateStr)) {
                                    selectedDates.push(dateStr);
                                }
                            });
                        }
                        

                        const validation = validateAndNormalizeSelectedDates();
                        

                        if (!validation.valid) {
                            selectedCheckinDate = null;
                            selectedCheckoutDate = null;
                        }
                        
                        renderCalendar();
                        updateSelectedDatesInfo();
                        updatePayButton();
                    }
                } else if (dragStartDate) {
                    toggleDate(dragStartDate, false);
                }
                
                isDragging = false;
                isDragRemoving = false;
                dragStartDate = null;
                dragEndDate = null;
                draggedDates = new Set();
                
                setTimeout(() => {
                    wasDragging = false;
                }, 100);
            }

            window.renderChatCalendar = renderCalendar;

            renderCalendar();

            document.addEventListener('click', function(e) {
                if (!container.contains(e.target) && isRangeSelecting) {
                    isRangeSelecting = false;
                    rangeStartDate = null;
                    renderCalendar();
                    updateSelectedDatesInfo();
                }
            });
            
            container.addEventListener('mouseleave', function() {
                if (isDragging) {
                    const event = new MouseEvent('mouseup', { bubbles: true, cancelable: true });
                    document.dispatchEvent(event);
                }
            });
        }

        window.initChatCalendar = function() {
            const container = document.querySelector('.house-calendar-container[data-readonly="true"]');
            if (container && container.dataset.houseId) {
                const datesData = container.dataset.dates;
                let bookedDates = [];
                try {
                    bookedDates = datesData ? JSON.parse(datesData) : [];
                    bookedDates = bookedDates.map(date => {
                        if (typeof date === 'string') {
                            return date.split('T')[0];
                        }
                        return date;
                    });
                } catch (e) {
                    console.warn('Ошибка парсинга дат календаря:', e);
                    bookedDates = [];
                }
                if (window.renderChatCalendar) {
                    window.renderChatCalendar();
                } else {
                    initChatCalendar();
                }
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initChatCalendar);
        } else {
            initChatCalendar();
        }
    })();
</script>
@endsection


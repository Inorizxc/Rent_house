/**
 * Модуль для работы с чатом
 */

import { apiFetch, formatDate, scrollToBottom, isScrolledToBottom } from '../modules/utils.js';
import { showBanNotification } from '../modules/notifications.js';
import { PollingManager } from '../modules/polling.js';

class ChatManager {
    constructor(config) {
        this.chatId = config.chatId;
        this.currentUserId = config.currentUserId;
        this.lastMessageId = config.lastMessageId || 0;
        this.messagesContainer = document.getElementById('chatMessages');
        this.messageInput = document.getElementById('messageInput');
        this.sendButton = document.getElementById('sendButton');
        this.chatForm = document.getElementById('chatForm');
        
        this.pollingManager = new PollingManager(() => this.loadNewMessages(), 2000);
        
        this.init();
    }

    init() {
        // Обработчик отправки формы
        if (this.chatForm) {
            this.chatForm.addEventListener('submit', (e) => this.sendMessage(e));
        }

        // Обработчик нажатия клавиш
        if (this.messageInput) {
            this.messageInput.addEventListener('input', () => this.autoResize(this.messageInput));
            this.messageInput.addEventListener('keydown', (e) => this.handleKeyDown(e));
        }

        // Прокручиваем вниз при загрузке страницы
        window.addEventListener('load', () => {
            scrollToBottom(this.messagesContainer);
            this.pollingManager.init();
        });
    }

    autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    handleKeyDown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            this.sendMessage(event);
        }
    }

    async sendMessage(event) {
        event.preventDefault();
        
        const message = this.messageInput.value.trim();
        if (!message) {
            return;
        }

        this.sendButton.disabled = true;
        this.sendButton.textContent = 'Отправка...';

        try {
            const response = await fetch(`/chats/${this.chatId}/message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            if (!response.ok) {
                const err = await response.json().catch(() => ({ 
                    error: `Ошибка ${response.status}: ${response.statusText}` 
                }));
                const errorMessage = err.error || err.message || 'Ошибка при отправке сообщения';
                if (response.status === 403 || errorMessage.includes('заблокирован')) {
                    showBanNotification(errorMessage);
                }
                throw new Error(errorMessage);
            }

            const data = await response.json();

            if (data.success && data.message) {
                this.messageInput.value = '';
                this.messageInput.style.height = 'auto';
                this.lastMessageId = data.message.message_id;
                this.addMessageToChat(data.message, true);
                scrollToBottom(this.messagesContainer);
            } else {
                const errorMessage = data.error || 'Неизвестная ошибка при отправке сообщения';
                if (errorMessage.includes('заблокирован')) {
                    showBanNotification(errorMessage);
                }
                throw new Error(errorMessage);
            }
        } catch (error) {
            console.error('Error:', error);
            const errorMessage = error.message || 'Ошибка при отправке сообщения';
            if (errorMessage.includes('заблокирован')) {
                showBanNotification(errorMessage);
            } else {
                alert(errorMessage);
            }
        } finally {
            this.sendButton.disabled = false;
            this.sendButton.textContent = 'Отправить';
        }
    }

    addMessageToChat(messageData, isOwn) {
        // Проверяем, не добавлено ли уже это сообщение
        const existingMessage = this.messagesContainer.querySelector(
            `[data-message-id="${messageData.message_id}"]`
        );
        if (existingMessage) {
            return;
        }

        // Удаляем сообщение "Нет сообщений", если оно есть
        const emptyMessages = this.messagesContainer.querySelector('.empty-messages');
        if (emptyMessages) {
            emptyMessages.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isOwn ? 'own' : 'other'}`;
        messageDiv.setAttribute('data-message-id', messageData.message_id);

        const authorFio = messageData.user
            ? `${messageData.user.sename || ''} ${messageData.user.name || ''} ${messageData.user.patronymic || ''}`.trim() ||
              `Пользователь #${messageData.user.user_id}`
            : 'Пользователь';

        if (!isOwn) {
            const authorDiv = document.createElement('div');
            authorDiv.className = 'message-author';
            authorDiv.textContent = authorFio;
            messageDiv.appendChild(authorDiv);
        }

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.innerHTML = this.processMessageText(messageData.message);
        messageDiv.appendChild(contentDiv);

        const metaDiv = document.createElement('div');
        metaDiv.className = 'message-meta';
        metaDiv.textContent = formatDate(messageData.created_at);
        messageDiv.appendChild(metaDiv);

        this.messagesContainer.appendChild(messageDiv);
        scrollToBottom(this.messagesContainer);
    }

    processMessageText(text) {
        // Заменяем "Заказ #123" на кликабельную ссылку
        return text.replace(/Заказ\s*#(\d+)/gi, (match, orderId) => {
            return `<a href="/orders/${orderId}" class="order-link" style="color: #4f46e5; text-decoration: underline; font-weight: 600;">Заказ #${orderId}</a>`;
        });
    }

    async loadNewMessages() {
        try {
            const response = await fetch(`/chats/${this.chatId}/messages?lastMessageId=${this.lastMessageId}`);
            const data = await response.json();

            if (data.messages && data.messages.length > 0) {
                const wasScrolledToBottom = isScrolledToBottom(this.messagesContainer);
                let hasNewMessages = false;

                data.messages.forEach(msg => {
                    if (msg.message_id > this.lastMessageId) {
                        const existingMessage = document.querySelector(`[data-message-id="${msg.message_id}"]`);
                        if (!existingMessage) {
                            this.addMessageToChat(msg, msg.user_id == this.currentUserId);
                            this.lastMessageId = msg.message_id;
                            hasNewMessages = true;
                        }
                    }
                });

                // Прокручиваем вниз только если пользователь был внизу или это новое сообщение
                if (hasNewMessages && (wasScrolledToBottom || data.messages.length > 0)) {
                    scrollToBottom(this.messagesContainer);
                }
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }
}

// Инициализация при загрузке страницы
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChat);
} else {
    initChat();
}

function initChat() {
    const chatId = window.chatConfig?.chatId;
    const currentUserId = window.chatConfig?.currentUserId;
    const lastMessageId = window.chatConfig?.lastMessageId || 0;

    if (chatId && currentUserId) {
        new ChatManager({
            chatId,
            currentUserId,
            lastMessageId
        });
    }
}


/**
 * Модуль для работы с меню пользователя
 */

class UserMenuManager {
    constructor() {
        this.toggle = document.getElementById('userMenuToggle');
        this.dropdown = document.getElementById('userDropdown');
        this.isOpen = false;
        this.chatLinkWrapper = document.getElementById('chatLinkWrapper');
        
        if (this.toggle && this.dropdown) {
            this.init();
        }
    }

    init() {
        this.toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        });

        // Обработка кликов внутри меню
        this.dropdown.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            const button = event.target.closest('button[type="submit"]');

            if (link) {
                event.stopPropagation();
                setTimeout(() => {
                    this.close();
                }, 200);
            }

            if (button) {
                event.stopPropagation();
            }
        });

        // Закрытие при клике вне меню
        document.addEventListener('click', (event) => {
            if (this.isOpen && 
                !this.toggle.contains(event.target) && 
                !this.dropdown.contains(event.target)) {
                this.close();
            }
        });

        // Закрытие по Escape
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });

        // Инициализация обновления счетчика непрочитанных сообщений
        if (this.chatLinkWrapper) {
            this.initUnreadCountUpdater();
        }
    }

    open() {
        this.dropdown.classList.add('show');
        this.toggle.classList.add('active');
        this.toggle.setAttribute('aria-expanded', 'true');
        this.isOpen = true;
    }

    close() {
        this.dropdown.classList.remove('show');
        this.toggle.classList.remove('active');
        this.toggle.setAttribute('aria-expanded', 'false');
        this.isOpen = false;
    }

    initUnreadCountUpdater() {
        // Получаем маршрут из data-атрибута или используем дефолтный
        const updateRoute = this.chatLinkWrapper.getAttribute('data-update-route') || '/chats/unread/count';
        
        const updateUnreadCount = () => {
            fetch(updateRoute, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const unreadCount = data.unreadCount || 0;
                const chatBadge = document.getElementById('chatBadge');

                if (unreadCount > 0) {
                    if (!chatBadge) {
                        const badge = document.createElement('span');
                        badge.className = 'chat-badge';
                        badge.id = 'chatBadge';
                        this.chatLinkWrapper.appendChild(badge);
                    }
                    const badge = document.getElementById('chatBadge');
                    if (badge) {
                        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    }
                } else {
                    if (chatBadge) {
                        chatBadge.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error updating unread count:', error);
            });
        };

        // Обновляем каждые 5 секунд
        updateUnreadCount();
        setInterval(updateUnreadCount, 5000);
    }
}

// Инициализация при загрузке DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new UserMenuManager();
    });
} else {
    new UserMenuManager();
}


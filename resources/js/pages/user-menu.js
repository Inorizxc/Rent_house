

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

        document.addEventListener('click', (event) => {
            if (this.isOpen && 
                !this.toggle.contains(event.target) && 
                !this.dropdown.contains(event.target)) {
                this.close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });

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

        updateUnreadCount();
        setInterval(updateUnreadCount, 5000);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new UserMenuManager();
    });
} else {
    new UserMenuManager();
}


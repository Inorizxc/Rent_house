<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    
    <script src="https://api-maps.yandex.ru/2.1/?apikey=a2cd05de-c1e4-457b-8092-a8b0ebd9db10&lang=ru_RU" type="text/javascript"></script>
    <script src="{{ asset('js/photo-carousel.js') }}"></script>


    
    <style>
        
        @yield('style')

        * {
        box-sizing: border-box;
    }
        /* ====== ШРИФТ ====== */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            padding-top: 57px; /* чтобы контент не прятался под шапкой */
            background: #f6f6f7;
        }

/* ====== HEADER ====== */
header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;

    background: #ffffff;
    border-bottom: 1px solid #e5e5e5;

    padding: 14px 24px;

    display: flex;
    justify-content: flex-end;
    align-items: center;

    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);

    z-index: 1000;
}

/* ====== NAVIGATION ====== */
header nav {
    display: flex;
    gap: 12px;
    align-items: center;
}

/* ССЫЛКИ */
header nav a {
    padding: 9px 18px;
    border-radius: 8px;

    font-size: 14px;
    font-weight: 500;

    text-decoration: none;

    color: #333;
    background: #ffffff;

    border: 1px solid #e0e0e0;

    transition: 0.2s ease;
}

/* Ховер */
header nav a:hover {
    background: #f2f2f2;
    border-color: #d0d0d0;
}

/* Акцентная кнопка (Dashboard) */
header nav a[href*="dashboard"] {
    border-color: #c7c7c7;
    background: #fafafa;
}

header nav a[href*="dashboard"]:hover {
    background: #f0f0f0;
}

/* Мобильная адаптация */
@media (max-width: 600px) {
    header {
        padding: 12px 16px;
    }

    header nav a {
        padding: 7px 14px;
        font-size: 13px;
    }
}   


/* Контейнер аватарки */
.user-menu {
    position: relative;
}

/* Кнопка-аватарка */
.user-avatar-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-size: 20px;
    cursor: pointer;
    outline: none;

    display: flex;
    align-items: center;
    justify-content: center;

    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.user-avatar-btn:hover {
    transform: scale(1.05);
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.user-avatar-btn:active {
    transform: scale(0.98);
}

.user-avatar-btn.active {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

/* Выпадающее меню */
.user-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;

    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    min-width: 200px;
    max-width: 250px;

    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08);

    padding: 8px 0;
    display: none;
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
    transition: opacity 0.2s ease, transform 0.2s ease;

    z-index: 1001;
    overflow: hidden;
}

/* Показ меню с анимацией */
.user-dropdown.show {
    display: block;
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* Пункты меню */
.user-dropdown-item {
    padding: 0;
    margin: 0;
    font-size: 14px;
    color: #374151;
    user-select: none;
    position: relative;
}

/* Имя пользователя в меню */
.user-dropdown-item.user-name {
    padding: 12px 16px;
    font-weight: 600;
    color: #111827;
    border-bottom: 1px solid #f3f4f6;
    margin-bottom: 4px;
    cursor: default;
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
}

.user-dropdown-item.user-name:hover {
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
}

/* Ссылки и кнопки внутри пунктов меню */
.user-dropdown-item a,
.user-dropdown-item button {
    all: unset;
    display: block;
    width: 100%;
    padding: 12px 16px;
    cursor: pointer;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
    box-sizing: border-box;
}

.user-dropdown-item a:hover,
.user-dropdown-item button:hover {
    background: linear-gradient(90deg, #f3f4f6 0%, #f9fafb 100%);
    color: #111827;
    padding-left: 20px;
}

.user-dropdown-item a:active,
.user-dropdown-item button:active {
    background: #e5e7eb;
    transform: scale(0.98);
}

/* Разделитель между пунктами */
.user-dropdown-item:not(.user-name) + .user-dropdown-item:not(.user-name) {
    border-top: 1px solid #f3f4f6;
}

/* Стили для ссылок в меню */
.dropdown-link {
    all: unset;
    display: block;
    width: 100%;
    padding: 12px 16px;
    cursor: pointer;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
    font-size: 14px;
}

.dropdown-link:hover {
    background: linear-gradient(90deg, #f3f4f6 0%, #f9fafb 100%);
    color: #111827;
    padding-left: 20px;
}

.dropdown-link:active {
    background: #e5e7eb;
    transform: scale(0.98);
}

/* Специальный стиль для кнопки выхода */
.user-dropdown-item form,
.dropdown-form {
    margin: 0;
    padding: 0;
    width: 100%;
}

.user-dropdown-item form button,
.dropdown-button {
    all: unset;
    display: block;
    width: 100%;
    padding: 12px 16px;
    cursor: pointer;
    color: #dc2626;
    font-weight: 500;
    text-align: left;
    text-decoration: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
    font-size: 14px;
}

.user-dropdown-item form button:hover,
.dropdown-button:hover {
    background: linear-gradient(90deg, #fee2e2 0%, #fef2f2 100%);
    color: #b91c1c;
    padding-left: 20px;
}

.user-dropdown-item form button:active,
.dropdown-button:active {
    background: #fecaca;
    transform: scale(0.98);
}

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap');

/* Общий стиль хедера */
.app-header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;

    height: 55px;
    padding: 0 24px;

    background: #ffffff;
    border-bottom: 1px solid #e5e5e5;

    display: flex;
    align-items: center;
    justify-content: space-between;

    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

/* ЛОГОТИП */
.app-header .logo {
    font-family: 'Poppins', sans-serif;
    font-size: 26px;
    font-weight: 600;
    letter-spacing: -0.5px;

    color: #1c1c1c;
    text-decoration: none;

    transition: opacity .2s ease;
}

.app-header .logo:hover {
    opacity: 0.7;
}

/* Навигация справа */
.nav-links {
    display: flex;
    gap: 12px;
    align-items: center;
}

.nav-links a {
    padding: 9px 18px;
    font-size: 14px;
    text-decoration: none;
    font-weight: 500;

    border-radius: 8px;
    border: 1px solid #e0e0e0;
    background: #fff;
    color: #333;

    transition: 0.2s ease;
}

    .nav-links a:hover {
        background: #f2f2f2;
        border-color: #d0d0d0;
    }

    /* Индикатор непрочитанных сообщений */
    .chat-link-wrapper {
        position: relative;
        display: inline-block;
    }

    .chat-badge {
        position: absolute;
        top: -8px;
        left: -8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-radius: 10px;
        border: 2px solid #ffffff;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4), 0 1px 3px rgba(0, 0, 0, 0.2);
        font-size: 11px;
        font-weight: 700;
        color: #ffffff;
        line-height: 1;
        letter-spacing: -0.3px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    </style>

    @yield('head')
</head>
<body>
    <header class="app-header">
        <a href="{{ url('/map') }}" class="logo">Zlovito</a>
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        @php
                            $currentUser = auth()->user();
                            $unreadCount = 0;
                            
                            if ($currentUser) {
                                // Получаем все чаты пользователя
                                $chats = \App\Models\Chat::where('user_id', $currentUser->user_id)
                                    ->orWhere('rent_dealer_id', $currentUser->user_id)
                                    ->get();
                                
                                // Подсчитываем чаты с непрочитанными сообщениями
                                foreach ($chats as $chat) {
                                    $lastMessage = \App\Models\Message::where('chat_id', $chat->chat_id)
                                        ->latest('created_at')
                                        ->first();
                                    
                                    if (!$lastMessage) {
                                        continue;
                                    }

                                    // Определяем, когда пользователь последний раз просматривал чат
                                    $lastReadAt = null;
                                    if ($chat->user_id == $currentUser->user_id) {
                                        $lastReadAt = $chat->user_last_read_at;
                                    } else {
                                        $lastReadAt = $chat->rent_dealer_last_read_at;
                                    }

                                    // Если последнее сообщение отправлено не текущим пользователем
                                    // и оно было создано после последнего просмотра (или чат никогда не просматривался)
                                    if ($lastMessage->user_id != $currentUser->user_id) {
                                        if (!$lastReadAt || $lastMessage->created_at > $lastReadAt) {
                                            $unreadCount++;
                                        }
                                    }
                                }
                            }
                        @endphp
                        <div class="chat-link-wrapper" id="chatLinkWrapper">
                            <a
                                href="{{ route('chats.index') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                            >
                                Чат
                            </a>
                            @if($unreadCount > 0)
                                <span class="chat-badge" id="chatBadge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </div>
                        <div class="user-menu-wrapper" style="position:relative;">
                            <div class="user-menu">
                                <button
                                    class="user-avatar-btn"
                                    id="userMenuToggle"
                                    type="button"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    😊
                                </button>

                                <div class="user-dropdown" id="userDropdown" role="menu">
                                    @php
                                        $currentUser = auth()->user();
                                        $userName = trim(($currentUser->name ?? '') . ' ' . ($currentUser->sename ?? ''));
                                        $userName = $userName ?: 'Пользователь';
                                    @endphp
                                    <div class="user-dropdown-item user-name">
                                        {{ $userName }}
                                    </div>
                                    <div class="user-dropdown-item">
                                        <a href="{{ route('profile.show', auth()->id()) }}" class="dropdown-link">
                                            Профиль
                                        </a>
                                    </div>

                                    @if(auth()->check() && auth()->user()->role_id == '1')
                                    <div class="user-dropdown-item">
                                        <a href="{{ route('admin.panel') }}" class="dropdown-link">
                                            Админ-панель
                                        </a>
                                    </div>
                                    @endif

                                    <div class="user-dropdown-item">
                                        <form method="POST" action="{{ route('logout') }}" class="dropdown-form">
                                            @csrf
                                            <button type="submit" class="dropdown-button">
                                                Выход
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            Авторизация
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Регистрация
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        @auth
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toggle = document.getElementById('userMenuToggle');
                const dropdown = document.getElementById('userDropdown');

                if (!toggle || !dropdown) {
                    return;
                }

                let isOpen = false;

                const openDropdown = () => {
                    dropdown.classList.add('show');
                    toggle.classList.add('active');
                    toggle.setAttribute('aria-expanded', 'true');
                    isOpen = true;
                };

                const closeDropdown = () => {
                    dropdown.classList.remove('show');
                    toggle.classList.remove('active');
                    toggle.setAttribute('aria-expanded', 'false');
                    isOpen = false;
                };

                // Обработка клика по кнопке переключения
                toggle.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (isOpen) {
                        closeDropdown();
                    } else {
                        openDropdown();
                    }
                });

                // Обработка кликов внутри меню - не мешаем работе ссылок и кнопок
                dropdown.addEventListener('click', (event) => {
                    const link = event.target.closest('a');
                    const button = event.target.closest('button[type="submit"]');
                    
                    // Для ссылок - закрываем меню после небольшой задержки
                    if (link) {
                        event.stopPropagation(); // Предотвращаем закрытие через document click
                        setTimeout(() => {
                            closeDropdown();
                        }, 200);
                    }
                    
                    // Для кнопок формы - не закрываем сразу, форма отправится
                    // Меню закроется при переходе на другую страницу
                    if (button) {
                        event.stopPropagation();
                    }
                });

                // Закрытие при клике вне меню
                document.addEventListener('click', (event) => {
                    if (isOpen && !toggle.contains(event.target) && !dropdown.contains(event.target)) {
                        closeDropdown();
                    }
                });

                // Закрытие по Escape
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && isOpen) {
                        closeDropdown();
                    }
                });

                // Обновление индикатора непрочитанных сообщений в реальном времени
                const chatLinkWrapper = document.getElementById('chatLinkWrapper');
                
                if (chatLinkWrapper) {
                    function updateUnreadCount() {
                        fetch('{{ route("chats.unread.count") }}', {
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
                                // Показываем индикатор, если его нет
                                if (!chatBadge) {
                                    const badge = document.createElement('span');
                                    badge.className = 'chat-badge';
                                    badge.id = 'chatBadge';
                                    chatLinkWrapper.appendChild(badge);
                                }
                                // Обновляем число в значке
                                const badge = document.getElementById('chatBadge');
                                if (badge) {
                                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                                }
                            } else {
                                // Скрываем индикатор, если он есть
                                if (chatBadge) {
                                    chatBadge.remove();
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error updating unread count:', error);
                        });
                    }

                    // Обновляем каждые 5 секунд
                    updateUnreadCount(); // Первое обновление сразу
                    setInterval(updateUnreadCount, 5000);
                }
            });
        </script>
        @endauth
    <div>
        @yield('main_content')
    </div>
</body>
</html>
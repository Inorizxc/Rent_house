<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    
    @vite(['resources/css/layout.css', 'resources/css/orders.css', 'resources/css/chat.css', 'resources/css/houses.css', 'resources/css/map.css', 'resources/css/users.css', 'resources/css/admin.css', 'resources/js/app.js'])

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


                        @if($currentUser)
                            @if ($currentUser->ban_reason !=null)
                        <div class="ban_reason" id="chatLinkWrapper">
                            
                            @if($currentUser->is_banned_permanently)
                            <a
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                            >
                                Причина бана: {{ $currentUser->ban_reason }}.  Забанен навсегда.
                            </a>
                            @else
                                <a
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                            >
                                Причина бана: {{ $currentUser->ban_reason }}.   Забанен до: {{ $currentUser->banned_until->format('d.m.Y H:i') }}.
                            </a>
                            @endif
                        </div>
                        @endif
                        @endif

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
            // Конфигурация для модуля меню пользователя
            document.getElementById('chatLinkWrapper')?.setAttribute('data-update-route', '{{ route("chats.unread.count") }}');
        </script>
        @endauth
    <div>
        @yield('main_content')
    </div>
</body>
</html>
@extends('layout')

@section('title', 'Чат #' . $chat->chat_id . ' - Админ-панель')

@section('main_content')
<div class="admin-container">
    <!-- Боковая панель -->
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Админ-панель</h2>
            <button class="sidebar-toggle" id="sidebarToggle" type="button">✕</button>
        </div>
        <nav class="table-list">
            <a href="{{ route('admin.chats') }}" 
               class="table-item {{ request()->routeIs('admin.chats*') ? 'active' : '' }}">
                📨 Все чаты
            </a>
            <a href="{{ route('admin.orders') }}" 
               class="table-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                📦 Все заказы
            </a>
            <a href="{{ route('admin.verification') }}" 
               class="table-item {{ request()->routeIs('admin.verification*') ? 'active' : '' }}">
                ✅ Верификация
            </a>
            <div style="padding: 12px 20px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-top: 1px solid #e5e5e5; margin-top: 8px; padding-top: 16px;">
                Таблицы БД
            </div>
            @php
                $tableNames = collect(\Illuminate\Support\Facades\DB::select("
                    SELECT name
                    FROM sqlite_master
                    WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
                    ORDER BY name
                "))->pluck('name');
            @endphp
            @foreach ($tableNames as $table)
                <a href="{{ route('admin.panel', ['table' => $table]) }}" 
                   class="table-item">
                    {{ $table }}
                </a>
            @endforeach
        </nav>
    </aside>

    <!-- Основной контент -->
    <main class="admin-content" id="mainContent">
        <div class="content-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" type="button">☰</button>
                <h1>Чат #{{ $chat->chat_id }}</h1>
            </div>
        </div>

        <a href="{{ route('admin.chats') }}" class="back-link">
            ← Назад к списку чатов
        </a>

        <div class="admin-card">
            <div class="chat-header">
                @php
                    $user = $chat->user;
                    $rentDealer = $chat->rentDealer;
                    
                    $userInitials = 'U';
                    if ($user) {
                        $name = trim(($user->name ?? '') . ' ' . ($user->sename ?? ''));
                        if ($name) {
                            $words = explode(' ', $name);
                            $userInitials = '';
                            foreach ($words as $word) {
                                if (!empty($word)) {
                                    $userInitials .= mb_substr($word, 0, 1, 'UTF-8');
                                    if (mb_strlen($userInitials, 'UTF-8') >= 2) break;
                                }
                            }
                            if (empty($userInitials)) $userInitials = mb_substr($name, 0, 1, 'UTF-8');
                        }
                        $userInitials = mb_strtoupper($userInitials, 'UTF-8');
                    }
                    
                    $dealerInitials = 'D';
                    if ($rentDealer) {
                        $name = trim(($rentDealer->name ?? '') . ' ' . ($rentDealer->sename ?? ''));
                        if ($name) {
                            $words = explode(' ', $name);
                            $dealerInitials = '';
                            foreach ($words as $word) {
                                if (!empty($word)) {
                                    $dealerInitials .= mb_substr($word, 0, 1, 'UTF-8');
                                    if (mb_strlen($dealerInitials, 'UTF-8') >= 2) break;
                                }
                            }
                            if (empty($dealerInitials)) $dealerInitials = mb_substr($name, 0, 1, 'UTF-8');
                        }
                        $dealerInitials = mb_strtoupper($dealerInitials, 'UTF-8');
                    }
                @endphp

                <div class="user-card">
                    <h3>Пользователь</h3>
                    @if($user)
                        <div class="user-info">
                            <div class="user-avatar">{{ $userInitials }}</div>
                            <div class="user-details">
                                <div class="user-name">{{ trim(($user->name ?? '') . ' ' . ($user->sename ?? '')) ?: 'Пользователь' }}</div>
                                <div class="user-email">{{ $user->email ?? '' }}</div>
                            </div>
                        </div>
                    @else
                        <div style="color: #9ca3af;">Пользователь удален</div>
                    @endif
                </div>

                <div class="user-card">
                    <h3>Арендодатель</h3>
                    @if($rentDealer)
                        <div class="user-info">
                            <div class="user-avatar">{{ $dealerInitials }}</div>
                            <div class="user-details">
                                <div class="user-name">{{ trim(($rentDealer->name ?? '') . ' ' . ($rentDealer->sename ?? '')) ?: 'Пользователь' }}</div>
                                <div class="user-email">{{ $rentDealer->email ?? '' }}</div>
                            </div>
                        </div>
                    @else
                        <div style="color: #9ca3af;">Арендодатель удален</div>
                    @endif
                </div>
            </div>

            <div class="messages-container">
                @if ($messages->isEmpty())
                    <div class="empty-messages">
                        <p>В этом чате пока нет сообщений.</p>
                    </div>
                @else
                    @foreach ($messages as $message)
                        @php
                            $messageUser = $message->user;
                            $isUser = $message->user_id == ($user ? $user->user_id : null);
                            
                            $messageInitials = 'U';
                            if ($messageUser) {
                                $name = trim(($messageUser->name ?? '') . ' ' . ($messageUser->sename ?? ''));
                                if ($name) {
                                    $words = explode(' ', $name);
                                    $messageInitials = '';
                                    foreach ($words as $word) {
                                        if (!empty($word)) {
                                            $messageInitials .= mb_substr($word, 0, 1, 'UTF-8');
                                            if (mb_strlen($messageInitials, 'UTF-8') >= 2) break;
                                        }
                                    }
                                    if (empty($messageInitials)) $messageInitials = mb_substr($name, 0, 1, 'UTF-8');
                                }
                                $messageInitials = mb_strtoupper($messageInitials, 'UTF-8');
                            }
                        @endphp
                        <div class="message {{ $isUser ? 'own' : '' }}">
                            <div class="message-avatar">{{ $messageInitials }}</div>
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="message-author">
                                        {{ $messageUser ? trim(($messageUser->name ?? '') . ' ' . ($messageUser->sename ?? '')) : 'Удаленный пользователь' }}
                                    </span>
                                    <span class="message-time">
                                        {{ $message->created_at->format('d.m.Y H:i') }}
                                    </span>
                                </div>
                                <div class="message-text">
                                    {{ $message->message }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const mainContent = document.getElementById('mainContent');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('closed');
            mainContent.classList.toggle('expanded');
        });
    }

    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !mobileSidebarToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });

    // Автопрокрутка к последнему сообщению
    const messagesContainer = document.querySelector('.messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>
@endsection


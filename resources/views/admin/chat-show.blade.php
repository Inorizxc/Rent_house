@extends('layout')

@section('title', 'Чат #' . $chat->chat_id . ' - Админ-панель')

@section('style')
    .admin-container {
        display: flex;
        min-height: calc(100vh - 57px);
        background: #f6f6f7;
    }

    .admin-sidebar {
        width: 280px;
        background: #ffffff;
        border-right: 1px solid #e5e5e5;
        position: fixed;
        left: 0;
        top: 57px;
        height: calc(100vh - 57px);
        overflow-y: auto;
        transition: transform 0.3s ease;
        z-index: 999;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.03);
    }

    .admin-sidebar.closed {
        transform: translateX(-100%);
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid #e5e5e5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1c1c1c;
    }

    .sidebar-toggle {
        background: none;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 6px 10px;
        cursor: pointer;
        font-size: 14px;
        color: #333;
        transition: all 0.2s ease;
    }

    .sidebar-toggle:hover {
        background: #f2f2f2;
        border-color: #d0d0d0;
    }

    .table-list {
        padding: 10px 0;
    }

    .table-item {
        display: block;
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        font-size: 14px;
    }

    .table-item:hover {
        background: #f9fafb;
        border-left-color: #667eea;
        padding-left: 22px;
    }

    .table-item.active {
        background: linear-gradient(90deg, #f3f4f6 0%, #f9fafb 100%);
        border-left-color: #667eea;
        font-weight: 500;
        color: #667eea;
    }

    .admin-content {
        flex: 1;
        margin-left: 280px;
        padding: 24px;
        transition: margin-left 0.3s ease;
    }

    .admin-content.expanded {
        margin-left: 0;
    }

    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .content-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #1c1c1c;
    }

    .mobile-sidebar-toggle {
        display: none;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px 14px;
        cursor: pointer;
        font-size: 14px;
        color: #333;
        transition: all 0.2s ease;
    }

    .mobile-sidebar-toggle:hover {
        background: #f2f2f2;
        border-color: #d0d0d0;
    }

    .admin-card {
        background: #ffffff;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .chat-header {
        display: flex;
        gap: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e5e5;
        margin-bottom: 20px;
    }

    .user-card {
        flex: 1;
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
    }

    .user-card h3 {
        margin: 0 0 8px 0;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 600;
        font-size: 18px;
    }

    .user-details {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 16px;
        margin-bottom: 4px;
    }

    .user-email {
        font-size: 13px;
        color: #6b7280;
    }

    .messages-container {
        max-height: 600px;
        overflow-y: auto;
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .message {
        margin-bottom: 16px;
        display: flex;
        gap: 12px;
    }

    .message.own {
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 600;
        font-size: 14px;
        flex-shrink: 0;
    }

    .message-content {
        max-width: 70%;
        background: #ffffff;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .message.own .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #ffffff;
        border-color: transparent;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .message-author {
        font-weight: 600;
        font-size: 13px;
        color: #667eea;
    }

    .message.own .message-author {
        color: rgba(255, 255, 255, 0.9);
    }

    .message-time {
        font-size: 11px;
        color: #9ca3af;
    }

    .message.own .message-time {
        color: rgba(255, 255, 255, 0.7);
    }

    .message-text {
        font-size: 14px;
        line-height: 1.5;
        color: #1f2937;
        word-wrap: break-word;
    }

    .message.own .message-text {
        color: #ffffff;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
        margin-bottom: 16px;
    }

    .back-link:hover {
        background: #f2f2f2;
        border-color: #d0d0d0;
    }

    .empty-messages {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }

        .admin-sidebar.open {
            transform: translateX(0);
        }

        .admin-content {
            margin-left: 0;
        }

        .mobile-sidebar-toggle {
            display: block;
        }

        .chat-header {
            flex-direction: column;
        }

        .message-content {
            max-width: 85%;
        }
    }
@endsection

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


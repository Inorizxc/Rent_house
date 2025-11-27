@extends('layout')

@section('title', 'Все чаты - Админ-панель')

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

    .chats-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .chats-table thead {
        background: #f9fafb;
    }

    .chats-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e5e5;
    }

    .chats-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
    }

    .chats-table tbody tr:hover {
        background: #f9fafb;
        cursor: pointer;
    }

    .chats-table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
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
    }

    .user-name {
        font-weight: 500;
        color: #1f2937;
    }

    .user-email {
        font-size: 12px;
        color: #6b7280;
    }

    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-primary {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .last-message {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #6b7280;
        font-size: 13px;
    }

    .pagination {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: center;
        margin-top: 24px;
        flex-wrap: wrap;
    }

    .pagination a,
    .pagination button {
        padding: 8px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        text-decoration: none;
        color: #333;
        background: #ffffff;
        font-size: 14px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .pagination a:hover:not(.disabled):not(.active),
    .pagination button:hover:not(.disabled) {
        background: #f2f2f2;
        border-color: #d0d0d0;
    }

    .pagination .active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #ffffff;
        border-color: transparent;
    }

    .pagination .disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .rows-per-page {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .rows-per-page label {
        font-size: 14px;
        color: #6b7280;
    }

    .rows-per-page input {
        width: 80px;
        padding: 6px 10px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
    }

    .filters {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-group label {
        font-size: 14px;
        color: #6b7280;
        white-space: nowrap;
    }

    .filter-group select {
        padding: 8px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
        background: #ffffff;
        cursor: pointer;
        min-width: 200px;
    }

    .filter-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
            <a href="{{ route('admin.bans') }}" 
               class="table-item {{ request()->routeIs('admin.bans*') ? 'active' : '' }}">
                🚫 Баны
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
                <h1>Все чаты</h1>
            </div>
        </div>

        <div class="admin-card">
            <form method="GET" action="{{ route('admin.chats') }}" class="filters">
                <div class="filter-group">
                    <label for="user_id">Пользователь:</label>
                    <select id="user_id" name="user_id" onchange="this.form.submit()">
                        <option value="">Все пользователи</option>
                        @if(isset($users) && $users)
                            @foreach ($users as $user)
                                <option value="{{ $user->user_id }}" {{ $userFilter == $user->user_id ? 'selected' : '' }}>
                                    {{ trim(($user->name ?? '') . ' ' . ($user->sename ?? '')) ?: 'Пользователь #' . $user->user_id }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="filter-group">
                    <label for="dealer_id">Арендодатель:</label>
                    <select id="dealer_id" name="dealer_id" onchange="this.form.submit()">
                        <option value="">Все арендодатели</option>
                        @if(isset($users) && $users)
                            @foreach ($users as $user)
                                <option value="{{ $user->user_id }}" {{ $dealerFilter == $user->user_id ? 'selected' : '' }}>
                                    {{ trim(($user->name ?? '') . ' ' . ($user->sename ?? '')) ?: 'Пользователь #' . $user->user_id }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <input type="hidden" name="per" value="{{ $limit }}">
            </form>

            <form method="GET" action="{{ route('admin.chats') }}" class="rows-per-page">
                <input type="hidden" name="page" value="1">
                @if($userFilter)
                    <input type="hidden" name="user_id" value="{{ $userFilter }}">
                @endif
                @if($dealerFilter)
                    <input type="hidden" name="dealer_id" value="{{ $dealerFilter }}">
                @endif
                <label for="per">Чатов на странице:</label>
                <input
                    id="per"
                    name="per"
                    type="number"
                    min="1"
                    max="100"
                    value="{{ $limit }}"
                    onchange="this.form.submit()"
                >
            </form>

            @if ($chats->isEmpty())
                <div style="text-align: center; padding: 40px 20px; color: #6b7280;">
                    <p>Нет чатов для отображения.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="chats-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Пользователь</th>
                                <th>Арендодатель</th>
                                <th>Последнее сообщение</th>
                                <th>Сообщений</th>
                                <th>Обновлен</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chats as $chat)
                                @php
                                    $user = $chat->user;
                                    $rentDealer = $chat->rentDealer;
                                    $lastMessage = $chat->last_message ?? null;
                                    
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
                                <tr onclick="window.location.href='{{ route('admin.chat.show', $chat->chat_id) }}'">
                                    <td><strong>#{{ $chat->chat_id }}</strong></td>
                                    <td>
                                        @if($user)
                                            <div class="user-info">
                                                <div class="user-avatar">{{ $userInitials }}</div>
                                                <div>
                                                    <div class="user-name">{{ trim(($user->name ?? '') . ' ' . ($user->sename ?? '')) ?: 'Пользователь' }}</div>
                                                    <div class="user-email">{{ $user->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">Удален</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rentDealer)
                                            <div class="user-info">
                                                <div class="user-avatar">{{ $dealerInitials }}</div>
                                                <div>
                                                    <div class="user-name">{{ trim(($rentDealer->name ?? '') . ' ' . ($rentDealer->sename ?? '')) ?: 'Пользователь' }}</div>
                                                    <div class="user-email">{{ $rentDealer->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">Удален</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lastMessage)
                                            <div class="last-message" title="{{ $lastMessage->message }}">
                                                {{ \Illuminate\Support\Str::limit($lastMessage->message, 50) }}
                                            </div>
                                            <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                                                {{ $lastMessage->created_at->format('d.m.Y H:i') }}
                                            </div>
                                        @else
                                            <span style="color: #9ca3af; font-style: italic;">Нет сообщений</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $chat->message_count }}</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; color: #6b7280;">
                                            {{ $chat->updated_at->format('d.m.Y H:i') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                @php
                    $p = $page;
                    $N = $pages;
                    $items = [];

                    if ($N <= 7) {
                        $items = range(1, $N);
                    } elseif ($p <= 4) {
                        $items = [1, 2, 3, 4, 5, 'dots', $N];
                    } elseif ($p >= $N - 3) {
                        $items = [1, 'dots', $N-4, $N-3, $N-2, $N-1, $N];
                    } else {
                        $items = [1, 'dots', $p-1, $p, $p+1, 'dots', $N];
                    }
                @endphp

                @if ($pages > 1)
                    @php
                        $queryParams = [];
                        if ($userFilter) $queryParams['user_id'] = $userFilter;
                        if ($dealerFilter) $queryParams['dealer_id'] = $dealerFilter;
                        $queryString = http_build_query($queryParams);
                    @endphp
                    <div class="pagination">
                        <a href="?page={{ max(1, $p-1) }}&per={{ $limit }}{{ $queryString ? '&' . $queryString : '' }}"
                           class="{{ $p==1 ? 'disabled' : '' }}">&lsaquo;</a>

                        @foreach ($items as $it)
                            @if ($it === 'dots')
                                <button type="button" class="pag-ellipsis" data-total="{{ $N }}">…</button>
                            @else
                                <a href="?page={{ $it }}&per={{ $limit }}{{ $queryString ? '&' . $queryString : '' }}"
                                   class="{{ $it==$p ? 'active' : '' }}">{{ $it }}</a>
                            @endif
                        @endforeach

                        <a href="?page={{ min($N, $p+1) }}&per={{ $limit }}{{ $queryString ? '&' . $queryString : '' }}"
                           class="{{ $p==$N ? 'disabled' : '' }}">&rsaquo;</a>
                    </div>
                @endif
            @endif
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

    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('pag-ellipsis')) {
            const total = parseInt(e.target.getAttribute('data-total') || '1', 10);
            const input = prompt(`Введите номер страницы (1–${total})`);
            if (!input) return;
            let p = parseInt(input, 10);
            if (isNaN(p)) { alert('Введите число.'); return; }
            if (p < 1) p = 1;
            if (p > total) p = total;

            const params = new URLSearchParams(window.location.search);
            params.set('per', String({{ $limit }}));
            params.set('page', String(p));
            @if($userFilter)
                params.set('user_id', '{{ $userFilter }}');
            @endif
            @if($dealerFilter)
                params.set('dealer_id', '{{ $dealerFilter }}');
            @endif
            window.location.search = params.toString();
        }
    });
});
</script>
@endsection


@extends('layout')

@section('title', 'Все чаты - Админ-панель')

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


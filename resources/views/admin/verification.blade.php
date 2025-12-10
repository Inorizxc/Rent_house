@extends('layout')

@section('title', 'Верификация пользователей - Админ-панель')

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
                <h1>Верификация пользователей</h1>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="admin-card">
            <form method="GET" action="{{ route('admin.verification') }}" class="rows-per-page">
                <input type="hidden" name="page" value="1">
                <label for="per">Пользователей на странице:</label>
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

            @if ($users->isEmpty())
                <div class="empty-state">
                    <p>Нет пользователей, ожидающих верификацию.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="verification-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Пользователь</th>
                                <th>Текущая роль</th>
                                <th>Email</th>
                                <th>Телефон</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php
                                    $userInitials = 'U';
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
                                @endphp
                                <tr>
                                    <td><strong>#{{ $user->user_id }}</strong></td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">{{ $userInitials }}</div>
                                            <div class="user-details">
                                                <div class="user-name">{{ $name ?: 'Пользователь' }}</div>
                                                <div class="user-email">{{ $user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 13px; color: #6b7280;">
                                            {{ $user->roles ? $user->roles->name : 'Не указана' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->email ?? '—' }}</td>
                                    <td>{{ $user->phone ?? '—' }}</td>
                                    <td>
                                        <div style="font-size: 12px; color: #6b7280;">
                                            {{ $user->created_at->format('d.m.Y H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <form method="POST" 
                                                  action="{{ route('admin.verification.approve', $user->user_id) }}" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Вы уверены, что хотите подтвердить верификацию? Роль пользователя будет изменена на арендодателя.');">
                                                @csrf
                                                <button type="submit" class="btn btn-success">✅ Подтвердить</button>
                                            </form>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    onclick="openRejectModal({{ $user->user_id }})">
                                                ❌ Отклонить
                                            </button>
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
                    <div class="pagination">
                        <a href="?page={{ max(1, $p-1) }}&per={{ $limit }}"
                           class="{{ $p==1 ? 'disabled' : '' }}">&lsaquo;</a>

                        @foreach ($items as $it)
                            @if ($it === 'dots')
                                <button type="button" class="pag-ellipsis" data-total="{{ $N }}">…</button>
                            @else
                                <a href="?page={{ $it }}&per={{ $limit }}"
                                   class="{{ $it==$p ? 'active' : '' }}">{{ $it }}</a>
                            @endif
                        @endforeach

                        <a href="?page={{ min($N, $p+1) }}&per={{ $limit }}"
                           class="{{ $p==$N ? 'disabled' : '' }}">&rsaquo;</a>
                    </div>
                @endif
            @endif
        </div>
    </main>
</div>

<!-- Модальное окно для отклонения -->
<div class="reject-modal" id="rejectModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Отклонить верификацию</h3>
        </div>
        <form method="POST" id="rejectForm" action="">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="denied_days">Период блокировки (дней):</label>
                    <input 
                        type="number" 
                        id="denied_days" 
                        name="denied_days" 
                        value="7" 
                        min="1" 
                        max="365" 
                        required
                    >
                    <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                        Пользователь не сможет подать заявку на верификацию в течение указанного периода
                    </div>
                    <input 
                        type="text" 
                        id="reject_reason" 
                        name="reject_reason" 
                        required
                    >
                    <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                        Причина отклонения заявки
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Отмена</button>
                <button type="submit" class="btn btn-danger">Отклонить</button>
            </div>
        </form>
    </div>
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
            window.location.search = params.toString();
        }
    });
});

function openRejectModal(userId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '{{ route("admin.verification.reject", ":id") }}'.replace(':id', userId);
    modal.classList.add('show');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.remove('show');
}

// Закрытие модального окна при клике вне его
document.addEventListener('click', function(e) {
    const modal = document.getElementById('rejectModal');
    if (e.target === modal) {
        closeRejectModal();
    }
});
</script>
@endsection


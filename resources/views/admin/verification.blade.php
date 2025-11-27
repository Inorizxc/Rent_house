@extends('layout')

@section('title', 'Верификация пользователей - Админ-панель')

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

    .verification-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .verification-table thead {
        background: #f9fafb;
    }

    .verification-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e5e5;
    }

    .verification-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
    }

    .verification-table tbody tr:hover {
        background: #f9fafb;
    }

    .verification-table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 600;
        font-size: 16px;
    }

    .user-details {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 15px;
        margin-bottom: 2px;
    }

    .user-email {
        font-size: 12px;
        color: #6b7280;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
        text-decoration: none;
        display: inline-block;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
    }

    .btn-success:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-danger {
        background: #dc2626;
        color: #ffffff;
    }

    .btn-danger:hover {
        background: #b91c1c;
    }

    .reject-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .reject-modal.show {
        display: flex;
    }

    .modal-content {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        margin-bottom: 20px;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #1c1c1c;
    }

    .modal-body {
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-group input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
    }

    .form-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .modal-footer {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-success {
        background: #d1fae5;
        border: 1px solid #10b981;
        color: #065f46;
    }

    .alert-error {
        background: #fee2e2;
        border: 1px solid #ef4444;
        color: #991b1b;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
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

        .action-buttons {
            flex-direction: column;
            width: 100%;
        }

        .action-buttons .btn {
            width: 100%;
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


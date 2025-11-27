@extends('layout')

@section('title', 'Админ-панель')

@section('style')
    .admin-container {
        display: flex;
        min-height: calc(100vh - 57px);
        background: #f6f6f7;
    }

    /* Боковая панель */
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

    /* Основной контент */
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

    /* Карточки */
    .admin-card {
        background: #ffffff;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .admin-card h2 {
        margin: 0 0 20px 0;
        font-size: 20px;
        font-weight: 600;
        color: #1c1c1c;
    }

    /* Форма добавления */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-group .field-info {
        font-size: 11px;
        color: #6b7280;
        margin-top: 4px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #ffffff;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-danger {
        background: #dc2626;
        color: #ffffff;
        padding: 6px 12px;
        font-size: 12px;
    }

    .btn-danger:hover {
        background: #b91c1c;
    }

    /* Таблица */
    .table-wrapper {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .data-table thead {
        background: #f9fafb;
    }

    .data-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e5e5;
        white-space: nowrap;
    }

    .data-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
    }

    .data-table tbody tr:hover {
        background: #f9fafb;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Пагинация */
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

    /* Уведомления */
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

    /* Счетчик строк */
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

    /* Мобильная адаптация */
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

        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
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
            @foreach ($tables as $table)
                <a href="{{ route('admin.panel', ['table' => $table]) }}" 
                   class="table-item {{ $selectedTable === $table ? 'active' : '' }}">
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
                <h1>Админ-панель</h1>
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

        @if ($selectedTable)
            <!-- Форма добавления записи -->
            <div class="admin-card">
                <h2>Добавить запись в «{{ $selectedTable }}»</h2>
                <form method="POST" action="{{ route('admin.panel') }}">
                    @csrf
                    <input type="hidden" name="table" value="{{ $selectedTable }}">

                    <div class="form-grid">
                        @php
                            $blocked = ['id', 'created_at', 'updated_at', 'deleted_at'];
                        @endphp

                        @foreach ($columns as $col)
                            @continue(in_array($col->name, $blocked, true))

                            <div class="form-group">
                                <label for="{{ $col->name }}">
                                    {{ $col->name }}
                                </label>
                                <input
                                    type="text"
                                    id="{{ $col->name }}"
                                    name="{{ $col->name }}"
                                    value="{{ old($col->name) }}"
                                    @if ($col->notnull && $col->dflt_value === null) required @endif
                                    placeholder="Введите значение"
                                >
                                <span class="field-info">
                                    {{ $col->type ?: 'TEXT' }}
                                    @if ($col->notnull) • обязательное @endif
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Добавить запись
                    </button>
                </form>
            </div>

            <!-- Таблица данных -->
            <div class="admin-card">
                <h2>Таблица «{{ $selectedTable }}»</h2>

                <form method="GET" action="{{ route('admin.panel') }}" class="rows-per-page">
                    <input type="hidden" name="table" value="{{ $selectedTable }}">
                    <input type="hidden" name="page" value="1">
                    <label for="per">Строк на странице:</label>
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

                @if ($rows->isEmpty())
                    <div class="empty-state">
                        <p>Нет данных для отображения.</p>
                    </div>
                @else
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    @foreach(array_keys((array)$rows->first()) as $col)
                                        <th>{{ $col }}</th>
                                    @endforeach
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    <tr>
                                        @php
                                            $rowArray = (array)$row;
                                            $primaryKey = null;
                                            $primaryKeyValue = null;
                                            
                                            // Определяем primary key
                                            foreach ($columns as $col) {
                                                if ($col->pk == 1) {
                                                    $primaryKey = $col->name;
                                                    $primaryKeyValue = $rowArray[$col->name] ?? null;
                                                    break;
                                                }
                                            }
                                            
                                            // Если не найден, пробуем стандартные варианты
                                            if (!$primaryKey) {
                                                // Пробуем стандартные варианты
                                                if (isset($rowArray['id'])) {
                                                    $primaryKey = 'id';
                                                    $primaryKeyValue = $rowArray['id'];
                                                } elseif (isset($rowArray[$selectedTable . '_id'])) {
                                                    $primaryKey = $selectedTable . '_id';
                                                    $primaryKeyValue = $rowArray[$selectedTable . '_id'];
                                                } elseif (isset($rowArray['user_id'])) {
                                                    $primaryKey = 'user_id';
                                                    $primaryKeyValue = $rowArray['user_id'];
                                                }
                                            }
                                        @endphp
                                        @foreach ($rowArray as $val)
                                            <td>{{ is_null($val) ? '—' : $val }}</td>
                                        @endforeach
                                        <td>
                                            @if ($primaryKey && $primaryKeyValue)
                                                <form method="POST" 
                                                      action="{{ route('admin.panel.delete', ['table' => $selectedTable, 'id' => $primaryKeyValue]) }}" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить эту запись?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Удалить</button>
                                                </form>
                                            @endif
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
                            <a href="?table={{ urlencode($selectedTable) }}&page={{ max(1, $p-1) }}&per={{ $limit }}"
                               class="{{ $p==1 ? 'disabled' : '' }}">&lsaquo;</a>

                            @foreach ($items as $it)
                                @if ($it === 'dots')
                                    <button type="button" class="pag-ellipsis" data-total="{{ $N }}">…</button>
                                @else
                                    <a href="?table={{ urlencode($selectedTable) }}&page={{ $it }}&per={{ $limit }}"
                                       class="{{ $it==$p ? 'active' : '' }}">{{ $it }}</a>
                                @endif
                            @endforeach

                            <a href="?table={{ urlencode($selectedTable) }}&page={{ min($N, $p+1) }}&per={{ $limit }}"
                               class="{{ $p==$N ? 'disabled' : '' }}">&rsaquo;</a>
                        </div>
                    @endif
                @endif
            </div>
        @else
            <div class="admin-card">
                <div class="empty-state">
                    <p>Выберите таблицу из боковой панели для просмотра данных</p>
                </div>
            </div>
        @endif
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const mainContent = document.getElementById('mainContent');

    // Переключение боковой панели (десктоп)
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('closed');
            mainContent.classList.toggle('expanded');
        });
    }

    // Переключение боковой панели (мобильный)
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // Закрытие боковой панели при клике вне её (мобильный)
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !mobileSidebarToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });

    // Обработка клика по "…" в пагинации
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
            params.set('table', '{{ $selectedTable }}');
            params.set('per', String({{ $limit }}));
            params.set('page', String(p));
            window.location.search = params.toString();
        }
    });
});
</script>
@endsection


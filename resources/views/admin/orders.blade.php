@extends('layout')

@section('title', 'Все заказы - Админ-панель')

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
    }

    .filter-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .orders-table thead {
        background: #f9fafb;
    }

    .orders-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e5e5;
    }

    .orders-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
    }

    .orders-table tbody tr:hover {
        background: #f9fafb;
        cursor: pointer;
    }

    .orders-table tbody tr:last-child td {
        border-bottom: none;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-processing {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-refund {
        background: #fce7f3;
        color: #9f1239;
    }

    .house-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }

    .house-link:hover {
        text-decoration: underline;
    }

    .customer-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .customer-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 600;
        font-size: 12px;
    }

    .customer-name {
        font-weight: 500;
        color: #1f2937;
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

        .filters {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            width: 100%;
        }

        .filter-group select {
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
                <h1>Все заказы</h1>
            </div>
        </div>

        <div class="admin-card">
            <form method="GET" action="{{ route('admin.orders') }}" class="filters">
                <div class="filter-group">
                    <label for="status">Статус:</label>
                    <select id="status" name="status" onchange="this.form.submit()">
                        <option value="">Все статусы</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ $statusFilter === $status->value ? 'selected' : '' }}>
                                {{ $status->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="customer_id">Заказчик:</label>
                    <select id="customer_id" name="customer_id" onchange="this.form.submit()">
                        <option value="">Все заказчики</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->user_id }}" {{ $customerFilter == $customer->user_id ? 'selected' : '' }}>
                                {{ trim(($customer->name ?? '') . ' ' . ($customer->sename ?? '')) ?: 'Пользователь #' . $customer->user_id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="owner_id">Владелец дома:</label>
                    <select id="owner_id" name="owner_id" onchange="this.form.submit()">
                        <option value="">Все владельцы</option>
                        @foreach ($owners as $owner)
                            <option value="{{ $owner->user_id }}" {{ $ownerFilter == $owner->user_id ? 'selected' : '' }}>
                                {{ trim(($owner->name ?? '') . ' ' . ($owner->sename ?? '')) ?: 'Пользователь #' . $owner->user_id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="per" value="{{ $limit }}">
            </form>

            <form method="GET" action="{{ route('admin.orders') }}" class="rows-per-page">
                <input type="hidden" name="page" value="1">
                @if($statusFilter)
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                @if($customerFilter)
                    <input type="hidden" name="customer_id" value="{{ $customerFilter }}">
                @endif
                @if($ownerFilter)
                    <input type="hidden" name="owner_id" value="{{ $ownerFilter }}">
                @endif
                <label for="per">Заказов на странице:</label>
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

            @if ($orders->isEmpty())
                <div style="text-align: center; padding: 40px 20px; color: #6b7280;">
                    <p>Нет заказов для отображения.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дом</th>
                                <th>Заказчик</th>
                                <th>Владелец дома</th>
                                <th>Дата заказа</th>
                                <th>Кол-во дней</th>
                                <th>Статус</th>
                                <th>Создан</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $customer = $order->customer;
                                    $house = $order->house;
                                    $owner = $house ? $house->user : null;
                                    
                                    $customerInitials = 'U';
                                    if ($customer) {
                                        $name = trim(($customer->name ?? '') . ' ' . ($customer->sename ?? ''));
                                        if ($name) {
                                            $words = explode(' ', $name);
                                            $customerInitials = '';
                                            foreach ($words as $word) {
                                                if (!empty($word)) {
                                                    $customerInitials .= mb_substr($word, 0, 1, 'UTF-8');
                                                    if (mb_strlen($customerInitials, 'UTF-8') >= 2) break;
                                                }
                                            }
                                            if (empty($customerInitials)) $customerInitials = mb_substr($name, 0, 1, 'UTF-8');
                                        }
                                        $customerInitials = mb_strtoupper($customerInitials, 'UTF-8');
                                    }

                                    $ownerInitials = 'O';
                                    if ($owner) {
                                        $name = trim(($owner->name ?? '') . ' ' . ($owner->sename ?? ''));
                                        if ($name) {
                                            $words = explode(' ', $name);
                                            $ownerInitials = '';
                                            foreach ($words as $word) {
                                                if (!empty($word)) {
                                                    $ownerInitials .= mb_substr($word, 0, 1, 'UTF-8');
                                                    if (mb_strlen($ownerInitials, 'UTF-8') >= 2) break;
                                                }
                                            }
                                            if (empty($ownerInitials)) $ownerInitials = mb_substr($name, 0, 1, 'UTF-8');
                                        }
                                        $ownerInitials = mb_strtoupper($ownerInitials, 'UTF-8');
                                    }

                                    $statusClass = 'status-pending';
                                    $statusValue = $order->order_status->value ?? 'Рассмотрение';
                                    switch($order->order_status) {
                                        case \App\enum\OrderStatus::PENDING:
                                            $statusClass = 'status-pending';
                                            break;
                                        case \App\enum\OrderStatus::PROCESSING:
                                            $statusClass = 'status-processing';
                                            break;
                                        case \App\enum\OrderStatus::COMPLETED:
                                            $statusClass = 'status-completed';
                                            break;
                                        case \App\enum\OrderStatus::CANCELLED:
                                            $statusClass = 'status-cancelled';
                                            break;
                                        case \App\enum\OrderStatus::REFUND:
                                            $statusClass = 'status-refund';
                                            break;
                                    }
                                @endphp
                                <tr onclick="window.location.href='{{ route('admin.order.show', $order->order_id) }}'">
                                    <td><strong>#{{ $order->order_id }}</strong></td>
                                    <td>
                                        @if($house)
                                            <a href="{{ route('house.show', $house->house_id) }}" 
                                               class="house-link" 
                                               onclick="event.stopPropagation();">
                                                Дом #{{ $house->house_id }}
                                            </a>
                                            @if($house->adress)
                                                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                                    {{ \Illuminate\Support\Str::limit($house->adress, 40) }}
                                                </div>
                                            @endif
                                        @else
                                            <span style="color: #9ca3af;">Дом удален</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($customer)
                                            <div class="customer-info">
                                                <div class="customer-avatar">{{ $customerInitials }}</div>
                                                <div>
                                                    <div class="customer-name">{{ trim(($customer->name ?? '') . ' ' . ($customer->sename ?? '')) ?: 'Пользователь' }}</div>
                                                    <div style="font-size: 12px; color: #6b7280;">{{ $customer->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">Удален</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($owner)
                                            <div class="customer-info">
                                                <div class="customer-avatar">{{ $ownerInitials }}</div>
                                                <div>
                                                    <div class="customer-name">{{ trim(($owner->name ?? '') . ' ' . ($owner->sename ?? '')) ?: 'Пользователь' }}</div>
                                                    <div style="font-size: 12px; color: #6b7280;">{{ $owner->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">Удален</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->date_of_order)
                                            <div style="font-weight: 500;">{{ \Carbon\Carbon::parse($order->date_of_order)->format('d.m.Y') }}</div>
                                        @else
                                            <span style="color: #9ca3af;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $order->day_count ?? 0 }}</strong> {{ $order->day_count == 1 ? 'день' : ($order->day_count < 5 ? 'дня' : 'дней') }}
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ $statusValue }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; color: #6b7280;">
                                            {{ $order->created_at->format('d.m.Y H:i') }}
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
                        if ($statusFilter) $queryParams['status'] = $statusFilter;
                        if ($customerFilter) $queryParams['customer_id'] = $customerFilter;
                        if ($ownerFilter) $queryParams['owner_id'] = $ownerFilter;
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
            window.location.search = params.toString();
        }
    });
});
</script>
@endsection


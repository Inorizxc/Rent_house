@extends('layout')

@section('title', 'Заказ #' . $order->order_id . ' - Админ-панель')

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

    .order-header {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .info-card {
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
    }

    .info-card h3 {
        margin: 0 0 12px 0;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .info-label {
        font-size: 12px;
        color: #6b7280;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
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

    .user-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
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

    .house-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }

    .house-link:hover {
        text-decoration: underline;
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

        .order-header {
            grid-template-columns: 1fr;
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
                <h1>Заказ #{{ $order->order_id }}</h1>
            </div>
        </div>

        <a href="{{ route('admin.orders') }}" class="back-link">
            ← Назад к списку заказов
        </a>

        <div class="admin-card">
            @php
                $customer = $order->customer;
                $house = $order->house;
                
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

            <div class="order-header">
                <div class="info-card">
                    <h3>Статус заказа</h3>
                    <div>
                        <span class="status-badge {{ $statusClass }}">
                            {{ $statusValue }}
                        </span>
                    </div>
                </div>

                <div class="info-card">
                    <h3>Дата заказа</h3>
                    <div class="info-value">
                        {{ $order->date_of_order ? \Carbon\Carbon::parse($order->date_of_order)->format('d.m.Y') : '—' }}
                    </div>
                </div>

                <div class="info-card">
                    <h3>Количество дней</h3>
                    <div class="info-value">
                        {{ $order->day_count ?? 0 }} {{ $order->day_count == 1 ? 'день' : ($order->day_count < 5 ? 'дня' : 'дней') }}
                    </div>
                </div>

                <div class="info-card">
                    <h3>Дата создания</h3>
                    <div class="info-value">
                        {{ $order->created_at->format('d.m.Y H:i') }}
                    </div>
                    <div class="info-label">
                        Обновлен: {{ $order->updated_at->format('d.m.Y H:i') }}
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">
                <div class="info-card">
                    <h3>Заказчик</h3>
                    @if($customer)
                        <div class="user-card">
                            <div class="user-avatar">{{ $customerInitials }}</div>
                            <div class="user-details">
                                <div class="user-name">{{ trim(($customer->name ?? '') . ' ' . ($customer->sename ?? '')) ?: 'Пользователь' }}</div>
                                <div class="user-email">{{ $customer->email ?? '' }}</div>
                            </div>
                        </div>
                    @else
                        <div style="color: #9ca3af;">Заказчик удален</div>
                    @endif
                </div>

                <div class="info-card">
                    <h3>Владелец дома</h3>
                    @php
                        $owner = $house ? $house->user : null;
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
                    @endphp
                    @if($owner)
                        <div class="user-card">
                            <div class="user-avatar">{{ $ownerInitials }}</div>
                            <div class="user-details">
                                <div class="user-name">{{ trim(($owner->name ?? '') . ' ' . ($owner->sename ?? '')) ?: 'Пользователь' }}</div>
                                <div class="user-email">{{ $owner->email ?? '' }}</div>
                            </div>
                        </div>
                    @else
                        <div style="color: #9ca3af;">Владелец удален</div>
                    @endif
                </div>

                <div class="info-card">
                    <h3>Дом</h3>
                    @if($house)
                        <div>
                            <a href="{{ route('house.show', $house->house_id) }}" class="house-link">
                                Дом #{{ $house->house_id }}
                            </a>
                            @if($house->adress)
                                <div style="font-size: 14px; color: #6b7280; margin-top: 8px;">
                                    {{ $house->adress }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div style="color: #9ca3af;">Дом удален</div>
                    @endif
                </div>
            </div>

            @if($order->original_data)
                <div class="info-card">
                    <h3>Дополнительная информация</h3>
                    <pre style="background: #ffffff; padding: 12px; border-radius: 6px; border: 1px solid #e5e5e5; font-size: 12px; overflow-x: auto;">{{ json_encode(json_decode($order->original_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
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
});
</script>
@endsection


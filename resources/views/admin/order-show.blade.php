@extends('layout')

@section('title', 'Заказ #' . $order->order_id . ' - Админ-панель')

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
                        if ($order->isRefunded()) {
                            $statusValue = 'Возврат выполнен';
                        } else {
                            $statusValue = 'Ожидает подтверждения возврата';
                        }
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

            @if($order->total_amount)
                <div class="info-card">
                    <h3>Сумма заказа</h3>
                    <div class="info-value" style="font-size: 20px; font-weight: 600; color: #059669;">
                        {{ number_format((float)$order->total_amount, 2, ',', ' ') }} ₽
                    </div>
                </div>
            @endif

            @if($order->order_status === \App\enum\OrderStatus::REFUND)
                @if($order->isRefunded())
                    <div class="info-card" style="border: 2px solid #10b981; background: #d1fae5;">
                        <h3 style="color: #065f46;">✅ Возврат средств выполнен</h3>
                        <p style="color: #065f46; margin: 12px 0;">
                            Возврат средств был выполнен {{ $order->refunded_at ? $order->refunded_at->format('d.m.Y в H:i') : '' }}.
                            Средства возвращены на баланс арендатора.
                        </p>
                    </div>
                @else
                    <div class="info-card" style="border: 2px solid #fbbf24; background: #fef3c7;">
                        <h3 style="color: #92400e;">Запрос на возврат средств</h3>
                        <p style="color: #92400e; margin: 12px 0;">Арендатор запросил возврат средств. Подтвердите возврат для перевода средств на баланс арендатора.</p>
                        <form method="POST" action="{{ route('admin.orders.refund', $order->order_id) }}" style="margin-top: 12px;">
                            @csrf
                            <button type="submit" class="btn-primary" style="background: #059669;" onclick="return confirm('Подтвердить возврат средств арендатору? Средства будут возвращены на баланс арендатора.')">
                                Подтвердить возврат
                            </button>
                        </form>
                    </div>
                @endif
            @elseif($order->order_status != \App\enum\OrderStatus::CANCELLED && !$order->isRefunded())
                <div class="info-card" style="border: 2px solid #ef4444;">
                    <h3 style="color: #ef4444;">Действия с заказом</h3>
                    <form method="POST" action="{{ route('admin.orders.refund', $order->order_id) }}" style="margin-top: 12px;">
                        @csrf
                        <button type="submit" class="btn-danger" onclick="return confirm('Выполнить возврат средств арендатору? Это действие нельзя отменить.')">
                            Вернуть средства арендатору
                        </button>
                    </form>
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


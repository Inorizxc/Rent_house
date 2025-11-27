@extends('layout')

@section('title', 'Заказ #' . $order->order_id)

@section('style')
    body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f6f6f7;
    }

    .page-wrapper {
        padding: 10px 24px 24px;
        display: flex;
        justify-content: center;
    }

    .order-container {
        max-width: 1200px;
        width: 100%;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .order-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
        padding: 20px 24px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e5e7eb;
    }

    .order-title {
        font-size: 24px;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .order-subtitle {
        font-size: 14px;
        color: #6b7280;
        margin-top: 4px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2933;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-grid {
        display: grid;
        gap: 12px;
    }

    .info-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px dashed #e5e7eb;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
    }

    .info-value {
        font-size: 14px;
        color: #111827;
    }

    .info-value a {
        color: #4f46e5;
        text-decoration: none;
        transition: color 0.2s;
    }

    .info-value a:hover {
        color: #4338ca;
        text-decoration: underline;
    }

    .order-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-transform: capitalize;
        white-space: nowrap;
        letter-spacing: 0.3px;
    }

    .order-status-pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 1px solid #fbbf24;
        box-shadow: 0 2px 4px rgba(146, 64, 14, 0.1);
    }

    .order-status-processing {
        background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
        color: #1e40af;
        border: 1px solid #3b82f6;
        box-shadow: 0 2px 4px rgba(30, 64, 175, 0.1);
    }

    .order-status-completed {
        background: linear-gradient(135deg, #d1fae5 0%, #6ee7b7 100%);
        color: #065f46;
        border: 1px solid #10b981;
        box-shadow: 0 2px 4px rgba(6, 95, 70, 0.1);
    }

    .order-status-cancelled {
        background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%);
        color: #991b1b;
        border: 1px solid #ef4444;
        box-shadow: 0 2px 4px rgba(153, 27, 27, 0.1);
    }

    .order-status-refund {
        background: linear-gradient(135deg, #fce7f3 0%, #f9a8d4 100%);
        color: #9f1239;
        border: 1px solid #ec4899;
        box-shadow: 0 2px 4px rgba(159, 18, 57, 0.1);
    }

    .house-preview {
        margin-top: 16px;
    }

    .house-photos {
        border-radius: 10px;
        overflow: hidden;
        background: #f3f4f6;
        border: 1px solid #e2e2e5;
        margin-bottom: 16px;
    }

    .house-photos-main {
        width: 100%;
        height: 300px;
        object-fit: cover;
        display: block;
    }

    .house-photos-thumbs {
        display: flex;
        gap: 6px;
        padding: 8px;
        overflow-x: auto;
        background: #f9fafb;
    }

    .house-photos-thumbs img {
        width: 64px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: border-color 0.15s;
    }

    .house-photos-thumbs img:hover {
        border-color: #a5b4fc;
    }

    .house-photos-empty {
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 14px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
        margin-top: 12px;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #6b7280;
        flex-shrink: 0;
    }

    .user-details {
        flex: 1;
    }

    .user-name {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }

    .user-name a {
        color: #111827;
        text-decoration: none;
    }

    .user-name a:hover {
        color: #4f46e5;
    }

    .user-role {
        font-size: 12px;
        color: #6b7280;
    }

    .actions {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .btn-primary,
    .btn-secondary {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #4338ca;
        border-color: #4338ca;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #ffffff;
        border-color: #e5e7eb;
        color: #111827;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        transform: translateY(-1px);
    }

    @media (max-width: 900px) {
        .order-container {
            grid-template-columns: 1fr;
        }
    }
@endsection

@section('main_content')
<div class="page-wrapper">
    <div class="order-container">
        <div class="order-card">
            <div class="order-header">
                <div>
                    <h1 class="order-title">Заказ #{{ $order->order_id }}</h1>
                    <div class="order-subtitle">Создан {{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d.m.Y H:i') : '—' }}</div>
                </div>
                @if($order->order_status)
                    @php
                        $statusClass = match($order->order_status) {
                            \App\enum\OrderStatus::PENDING => 'pending',
                            \App\enum\OrderStatus::PROCESSING => 'processing',
                            \App\enum\OrderStatus::COMPLETED => 'completed',
                            \App\enum\OrderStatus::CANCELLED => 'cancelled',
                            \App\enum\OrderStatus::REFUND => 'refund',
                            default => 'pending'
                        };
                    @endphp
                    <span class="order-status-badge order-status-{{ $statusClass }}">
                        {{ $order->order_status->value }}
                    </span>
                @endif
            </div>

            <div class="section-title">Информация о заказе</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Номер заказа:</div>
                    <div class="info-value">#{{ $order->order_id }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Дата заезда:</div>
                    <div class="info-value">
                        @if($order->date_of_order)
                            {{ \Carbon\Carbon::parse($order->date_of_order)->format('d.m.Y') }}
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Количество дней:</div>
                    <div class="info-value">{{ $order->day_count ?? '—' }} дн.</div>
                </div>
                @if($order->date_of_order && $order->day_count)
                    @php
                        $checkin = \Carbon\Carbon::parse($order->date_of_order);
                        $checkout = $checkin->copy()->addDays((int)$order->day_count);
                    @endphp
                    <div class="info-row">
                        <div class="info-label">Дата выезда:</div>
                        <div class="info-value">{{ $checkout->format('d.m.Y') }}</div>
                    </div>
                @endif
                @if($order->house && $order->house->price_id)
                    @php
                        $pricePerDay = (float)$order->house->price_id;
                        $totalAmount = $pricePerDay * (int)($order->day_count ?? 0);
                    @endphp
                    <div class="info-row">
                        <div class="info-label">Цена за день:</div>
                        <div class="info-value" style="font-weight: 600; color: #059669;">
                            {{ number_format($pricePerDay, 0, ',', ' ') }} ₽
                        </div>
                    </div>
                    @if($order->day_count)
                        <div class="info-row">
                            <div class="info-label">Общая сумма:</div>
                            <div class="info-value" style="font-weight: 700; font-size: 16px; color: #047857;">
                                {{ number_format($totalAmount, 0, ',', ' ') }} ₽
                            </div>
                        </div>
                    @endif
                @endif
                <div class="info-row">
                    <div class="info-label">Статус:</div>
                    <div class="info-value">
                        @if($order->order_status)
                            <span class="order-status-badge order-status-{{ $statusClass }}">
                                {{ $order->order_status->value }}
                            </span>
                        @else
                            Неизвестно
                        @endif
                    </div>
                </div>
            </div>

            @if($order->house)
                <div class="section-title" style="margin-top: 24px;">Информация о доме</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Адрес:</div>
                        <div class="info-value">
                            <a href="{{ route('houses.show', $order->house->house_id) }}">
                                {{ $order->house->adress ?? 'Не указан' }}
                            </a>
                        </div>
                    </div>
                    @if($order->house->area)
                        <div class="info-row">
                            <div class="info-label">Площадь:</div>
                            <div class="info-value">{{ $order->house->area }} м²</div>
                        </div>
                    @endif
                </div>

                @if($order->house->photo && $order->house->photo->count() > 0)
                    <div class="house-preview">
                        <div class="house-photos">
                            @php
                                $photos = $order->house->photo->filter(fn($photo) => !empty($photo->path));
                            @endphp
                            @if($photos->count() > 0)
                                <img
                                    src="{{ asset('storage/' . $photos->first()->path) }}"
                                    alt="{{ $photos->first()->name ?? 'Фото дома' }}"
                                    class="house-photos-main"
                                    id="mainHousePhoto"
                                >
                                @if($photos->count() > 1)
                                    <div class="house-photos-thumbs">
                                        @foreach($photos as $photo)
                                            <img
                                                src="{{ asset('storage/' . $photo->path) }}"
                                                alt="{{ $photo->name ?? 'Фото' }}"
                                                data-full="{{ asset('storage/' . $photo->path) }}"
                                                onclick="document.getElementById('mainHousePhoto').src = this.dataset.full;"
                                            >
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <div class="order-card" style="position: sticky; top: 67px; align-self: flex-start; height: auto;">
            <div class="section-title">Участники заказа</div>
            
            @if($isCustomer && $order->house && $order->house->user)
                <div class="user-info">
                    <div class="user-avatar">
                        {{ mb_substr($order->house->user->name ?? 'В', 0, 1) }}
                    </div>
                    <div class="user-details">
                        <div class="user-name">
                            <a href="{{ route('profile.show', $order->house->user_id) }}">
                                {{ trim(($order->house->user->name ?? '') . ' ' . ($order->house->user->sename ?? '')) ?: 'Пользователь #' . $order->house->user_id }}
                            </a>
                        </div>
                        <div class="user-role">Владелец дома</div>
                    </div>
                </div>
            @endif

            @if($isOwner && $order->customer)
                <div class="user-info">
                    <div class="user-avatar">
                        {{ mb_substr($order->customer->name ?? 'З', 0, 1) }}
                    </div>
                    <div class="user-details">
                        <div class="user-name">
                            <a href="{{ route('profile.show', $order->customer_id) }}">
                                {{ trim(($order->customer->name ?? '') . ' ' . ($order->customer->sename ?? '')) ?: 'Пользователь #' . $order->customer_id }}
                            </a>
                        </div>
                        <div class="user-role">Заказчик</div>
                    </div>
                </div>
            @endif

            @if($order->house && $order->house->price_id)
                @php
                    $pricePerDay = (float)$order->house->price_id;
                    $totalAmount = $pricePerDay * (int)($order->day_count ?? 0);
                @endphp
                <div class="section-title" style="margin-top: 24px;">Стоимость</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Цена за день:</div>
                        <div class="info-value" style="font-weight: 600; color: #059669;">
                            {{ number_format($pricePerDay, 0, ',', ' ') }} ₽
                        </div>
                    </div>
                    @if($order->day_count)
                        <div class="info-row" style="border-bottom: 2px solid #10b981; padding-bottom: 12px; margin-bottom: 8px;">
                            <div class="info-label" style="font-weight: 600; font-size: 15px;">Итого:</div>
                            <div class="info-value" style="font-weight: 700; font-size: 18px; color: #047857;">
                                {{ number_format($totalAmount, 0, ',', ' ') }} ₽
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($isCustomer && $order->house)
                <div class="actions">
                    <a href="{{ route('house.chat', $order->house->house_id) }}" class="btn-primary">
                        Написать владельцу
                    </a>
                </div>
            @endif

            @if($order->house)
                <div class="actions" style="margin-top: 12px;">
                    <a href="{{ route('houses.show', $order->house->house_id) }}" class="btn-secondary">
                        Посмотреть дом
                    </a>
                </div>
            @endif

            <div class="actions" style="margin-top: 12px;">
                <a href="{{ route('profile.show', $isCustomer ? $order->customer_id : ($order->house ? $order->house->user_id : $currentUser->user_id)) }}" class="btn-secondary">
                    ← Назад к профилю
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


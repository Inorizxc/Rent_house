@extends('layout')

@section('title', 'Заказ #' . $order->order_id)

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
                        $statusText = $order->order_status->value;
                        if ($order->order_status === \App\enum\OrderStatus::REFUND) {
                            if ($order->isRefunded()) {
                                $statusText = 'Возврат выполнен';
                            } else {
                                $statusText = 'Ожидает подтверждения возврата';
                            }
                        }
                    @endphp
                    <span class="order-status-badge order-status-{{ $statusClass }}">
                        {{ $statusText }}
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

            @if($order->house && $order->house->price_id && $order->day_count)
                @php
                    $pricePerDay = (float)$order->house->price_id;
                    $totalAmount = $pricePerDay * (int)($order->day_count ?? 0);
                @endphp
                <div class="section-title" style="margin-top: 24px;">Суммарная стоимость</div>
                <div class="order-total-section">
                    <div class="order-total-row">
                        <div class="order-total-label">Цена за день:</div>
                        <div class="order-total-value">{{ number_format($pricePerDay, 0, ',', ' ') }} ₽</div>
                    </div>
                    <div class="order-total-row">
                        <div class="order-total-label">Количество дней:</div>
                        <div class="order-total-value">{{ $order->day_count }} дн.</div>
                    </div>
                    <div class="order-total-row order-total-final">
                        <div class="order-total-label">Итого:</div>
                        <div class="order-total-value-final">{{ number_format($totalAmount, 0, ',', ' ') }} ₽</div>
                    </div>
                </div>
            @endif

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

        <div class="order-card order-card-sticky">
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

            @if($isOwner && $order->order_status != \App\enum\OrderStatus::COMPLETED && $order->order_status != \App\enum\OrderStatus::REFUND)
                <div class="actions">
                    <form method="POST" action="{{ route('orders.approve', $order->order_id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-primary" onclick="return confirm('Подтвердить заказ и перевести средства на ваш баланс?')">
                            Подтвердить заказ
                        </button>
                    </form>
                </div>
            @endif

            @if($isOwner && $order->order_status === \App\enum\OrderStatus::REFUND && !$order->isRefunded())
                <div class="actions" style="margin-top: {{ $isOwner && $order->order_status != \App\enum\OrderStatus::COMPLETED && $order->order_status != \App\enum\OrderStatus::REFUND ? '12px' : '0' }};">
                    <form method="POST" action="{{ route('orders.refund.approve', $order->order_id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-primary" style="background: #059669; color: white;" onclick="return confirm('Подтвердить возврат средств арендатору? Средства будут возвращены на баланс арендатора.')">
                            Подтвердить возврат
                        </button>
                    </form>
                </div>
            @endif

            @if($isOwner && $order->order_status != \App\enum\OrderStatus::REFUND && $order->order_status != \App\enum\OrderStatus::CANCELLED && $order->order_status != \App\enum\OrderStatus::COMPLETED && !$order->isRefunded())
                <div class="actions" style="margin-top: {{ $isOwner && $order->order_status != \App\enum\OrderStatus::COMPLETED && $order->order_status != \App\enum\OrderStatus::REFUND ? '12px' : '0' }};">
                    <form method="POST" action="{{ route('orders.refund.approve', $order->order_id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-secondary" style="background: #ef4444; color: white;" onclick="return confirm('Вернуть средства арендатору? Средства будут возвращены на баланс арендатора.')">
                            Вернуть средства
                        </button>
                    </form>
                </div>
            @endif

            @if($isCustomer && $order->order_status != \App\enum\OrderStatus::REFUND && $order->order_status != \App\enum\OrderStatus::CANCELLED)
                <div class="actions" style="margin-top: {{ ($isOwner && $order->order_status != \App\enum\OrderStatus::COMPLETED && $order->order_status != \App\enum\OrderStatus::REFUND) || ($isOwner && $order->order_status != \App\enum\OrderStatus::REFUND && $order->order_status != \App\enum\OrderStatus::CANCELLED && $order->order_status != \App\enum\OrderStatus::COMPLETED) ? '12px' : '0' }};">
                    <form method="POST" action="{{ route('orders.refund.approve', $order->order_id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-secondary" style="background: #ef4444; color: white;" onclick="return confirm('Запросить возврат средств? Запрос будет отправлен арендодателю и администратору для подтверждения.')">
                            Запросить возврат
                        </button>
                    </form>
                </div>
            @endif

            @if($isCustomer && $order->order_status === \App\enum\OrderStatus::REFUND)
                <div class="actions" style="margin-top: 12px;">
                    @if($order->isRefunded())
                        <div style="padding: 12px; background: #d1fae5; border: 1px solid #10b981; border-radius: 6px; color: #065f46;">
                            <strong>✅ Возврат средств выполнен</strong><br>
                            <small>Средства были возвращены на ваш баланс {{ $order->refunded_at ? $order->refunded_at->format('d.m.Y H:i') : '' }}.</small>
                        </div>
                    @else
                        <div style="padding: 12px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 6px; color: #92400e;">
                            <strong>Ожидается подтверждение возврата</strong><br>
                            <small>Ваш запрос на возврат средств отправлен. Ожидайте подтверждения от арендодателя или администратора.</small>
                        </div>
                    @endif
                </div>
            @endif

            @if($isCustomer && $order->house)
                <div class="actions" style="margin-top: 12px;">
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

<script>
(function() {
    const stickyCard = document.querySelector('.order-card-sticky');
    if (!stickyCard) return;

    const headerHeight = 57;
    const stickyOffset = 8;
    const stickyTop = headerHeight + stickyOffset;

    let initialTop = 0;
    let savedLeft = 0;
    let savedWidth = 0;

    function initSticky() {
        const rect = stickyCard.getBoundingClientRect();
        initialTop = rect.top + window.scrollY;
    }

    function handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop + stickyTop >= initialTop) {
            if (!stickyCard.classList.contains('is-sticky')) {
                const rect = stickyCard.getBoundingClientRect();
                savedLeft = rect.left;
                savedWidth = rect.width;
                
                stickyCard.classList.add('is-sticky');
                stickyCard.style.width = savedWidth + 'px';
                stickyCard.style.left = savedLeft + 'px';
            } else {
                const container = stickyCard.closest('.order-container');
                if (container) {
                    const containerRect = container.getBoundingClientRect();
                    const leftColumnWidth = (containerRect.width - 24) * 2 / 3;
                    const rightColumnLeft = containerRect.left + leftColumnWidth + 24;
                    stickyCard.style.left = rightColumnLeft + 'px';
                }
            }
        } else {
            if (stickyCard.classList.contains('is-sticky')) {
                stickyCard.classList.remove('is-sticky');
                stickyCard.style.width = '';
                stickyCard.style.left = '';
            }
        }
    }

    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initSticky();
                handleScroll();
            });
        } else {
            initSticky();
            handleScroll();
        }
    }
    
    init();
    
    window.addEventListener('scroll', handleScroll, { passive: true });
    
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (!stickyCard.classList.contains('is-sticky')) {
                initSticky();
            }
            handleScroll();
        }, 100);
    }, { passive: true });
})();
</script>
@endsection


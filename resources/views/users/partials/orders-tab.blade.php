@php
    $orders = $orders ?? collect();
    $currentUser = auth()->user();
    $user = $user ?? null;
    
    if ($currentUser && $currentUser->isBanned()) {
        abort(403, 'Заблокированные пользователи не могут просматривать заказы');
    }
@endphp

<div class="settings-tab-content">
    @if($orders->isEmpty())
        <div class="settings-section">
            <div class="settings-section-card">
                <div class="profile-empty">
                    У вас пока нет заказов.
                </div>
            </div>
        </div>
    @else
        {{-- Панель фильтров --}}
        <div class="orders-filters-section">
            <div class="orders-filters-card">
                <div class="orders-filters-content">
                    <div class="orders-filter-group-row">
                        <div class="orders-filter-group">
                            <label class="orders-filter-label">Тип заказа:</label>
                            <div class="orders-filter-buttons">
                                <label class="orders-filter-checkbox">
                                    <input type="checkbox" data-filter-role="customer" checked>
                                    <span class="orders-filter-checkbox-label">Мои заказы</span>
                                </label>
                                <label class="orders-filter-checkbox">
                                    <input type="checkbox" data-filter-role="owner" checked>
                                    <span class="orders-filter-checkbox-label">Заказы Покупателей</span>
                                </label>
                            </div>
                        </div>
                        <div class="orders-filter-group">
                            <label class="orders-filter-label">Статус:</label>
                            <div class="orders-filter-buttons">
                                <label class="orders-filter-checkbox">
                                    <input type="checkbox" data-filter-status="рассмотрение" checked>
                                    <span class="orders-filter-checkbox-label">Рассмотрение</span>
                                </label>
                                <label class="orders-filter-checkbox">
                                    <input type="checkbox" data-filter-status="обработка" checked>
                                    <span class="orders-filter-checkbox-label">Обработка</span>
                                </label>
                                <label class="orders-filter-checkbox">
                                    <input type="checkbox" data-filter-status="завершено" checked>
                                    <span class="orders-filter-checkbox-label">Завершено</span>
                                </label>
                                <label class="orders-filter-checkbox">
                                    <input type="checkbox" data-filter-status="отменено" checked>
                                    <span class="orders-filter-checkbox-label">Отменено</span>
                                </label>
                                <label class="orders-filter-checkbox">
                                    <input type="checkbox" data-filter-status="возврат" checked>
                                    <span class="orders-filter-checkbox-label">Возврат</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="orders-filter-group-row">
                        <div class="orders-filter-group">
                            <label class="orders-filter-label">Имя:</label>
                            <div class="orders-filter-input-wrapper">
                                <input type="text" 
                                       id="filter-name-input" 
                                       class="orders-filter-input" 
                                       placeholder="Введите имя..."
                                       autocomplete="off">
                                <div class="orders-autocomplete-dropdown" id="name-autocomplete"></div>
                            </div>
                        </div>
                        <div class="orders-filter-group">
                            <label class="orders-filter-label">Адрес:</label>
                            <div class="orders-filter-input-wrapper">
                                <input type="text" 
                                       id="filter-address-input" 
                                       class="orders-filter-input" 
                                       placeholder="Введите адрес..."
                                       autocomplete="off">
                                <div class="orders-autocomplete-dropdown" id="address-autocomplete"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="settings-section">
            <div class="orders-houses-grid" id="orders-houses-grid">
                @foreach($orders as $order)
                    @php
                        $house = $order->house;
                        $isOwnerOfHouse = $user && $house && $user->user_id == $house->user_id;
                        $isCustomer = $user && $order->customer_id == $user->user_id;
                        
                        $orderRole = $isOwnerOfHouse ? 'owner' : 'customer';
                        
                        $searchParts = array_filter([
                            $house ? ($house->adress ?? '') : '',
                            (string)($order->order_id ?? ''),
                            $order->order_status ? $order->order_status->value : '',
                            $order->date_of_order ?? '',
                            $house && $house->area ? (string)$house->area : '',
                        ], function($value) {
                            return $value !== '' && $value !== null;
                        });
                        $searchText = mb_strtolower(implode(' ', $searchParts), 'UTF-8');
                        $searchText = preg_replace('/\s+/u', ' ', $searchText);
                        $searchText = trim($searchText);
                        
                        $photoPayload = $house && $house->photo
                            ? $house->photo
                                ->filter(fn($photo) => !empty($photo->path))
                                ->map(fn($photo) => [
                                    'path' => $photo->path,
                                    'name' => $photo->name,
                                ])
                                ->values()
                            : collect();
                    @endphp
                    @php
                        $orderStatusValue = $order->order_status ? mb_strtolower($order->order_status->value, 'UTF-8') : '';
                        $customerName = $order->customer ? trim(($order->customer->name ?? '') . ' ' . ($order->customer->sename ?? '')) : '';
                        $ownerName = $house && $house->user ? trim(($house->user->name ?? '') . ' ' . ($house->user->sename ?? '')) : '';
                        $houseAddress = $house ? ($house->adress ?? '') : '';
                    @endphp
                    <div 
                        class="settings-section-card orders-house-card orders-compact-card order-role-{{ $orderRole }}" 
                        data-order-role="{{ $orderRole }}"
                        data-order-status="{{ $orderStatusValue }}"
                        data-customer-name="{{ mb_strtolower($customerName, 'UTF-8') }}"
                        data-customer-name-original="{{ htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') }}"
                        data-owner-name="{{ mb_strtolower($ownerName, 'UTF-8') }}"
                        data-owner-name-original="{{ htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8') }}"
                        data-house-address="{{ mb_strtolower($houseAddress, 'UTF-8') }}"
                        data-house-address-original="{{ htmlspecialchars($houseAddress, ENT_QUOTES, 'UTF-8') }}"
                    >
                        <div class="orders-compact-content">
                            <div class="orders-compact-header">
                                <div class="orders-compact-title">
                                    <a href="{{ route('orders.show', $order->order_id) }}" class="orders-compact-title-link">
                                        Заказ #{{ $order->order_id }}
                                    </a>
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
                                        $statusValue = mb_strtolower($order->order_status->value, 'UTF-8');
                                    @endphp
                                    <span class="order-status-badge order-status-{{ $statusClass }} order-status-{{ $statusValue }}">
                                        {{ $order->order_status->value }}
                                    </span>
                                @else
                                    <span class="order-status-badge order-status-pending">
                                        Неизвестно
                                    </span>
                                @endif
                            </div>

                            <div class="orders-compact-info">
                                @if($house)
                                    <div class="orders-compact-row">
                                        <span class="orders-compact-label">Адрес:</span>
                                        <a href="{{ route('houses.show', $house->house_id) }}" class="orders-compact-link">
                                            {{ $house->adress ?? 'Не указан' }}
                                        </a>
                                    </div>
                                @endif
                                
                                @if($orderRole === 'customer' && $house && $house->user)
                                    <div class="orders-compact-row">
                                        <span class="orders-compact-label">Владелец:</span>
                                        <a href="{{ route('profile.show', $house->user_id) }}" class="orders-compact-link">
                                            {{ trim(($house->user->name ?? '') . ' ' . ($house->user->sename ?? '')) ?: 'Пользователь #' . $house->user_id }}
                                        </a>
                                    </div>
                                @elseif($orderRole === 'owner' && $order->customer)
                                    <div class="orders-compact-row">
                                        <span class="orders-compact-label">Заказчик:</span>
                                        <a href="{{ route('profile.show', $order->customer_id) }}" class="orders-compact-link">
                                            {{ trim(($order->customer->name ?? '') . ' ' . ($order->customer->sename ?? '')) ?: 'Пользователь #' . $order->customer_id }}
                                        </a>
                                    </div>
                                @endif

                                <div class="orders-compact-row">
                                    <span class="orders-compact-label">Дата:</span>
                                    <span class="orders-compact-text">{{ $order->date_of_order ?? '—' }}</span>
                                </div>
                                
                                <div class="orders-compact-row">
                                    <span class="orders-compact-label">Дней:</span>
                                    <span class="orders-compact-text">{{ $order->day_count ?? '—' }} дн.</span>
                                </div>
                            </div>

                            @if($orderRole === 'customer' && $house)
                            <div class="orders-compact-actions">
                                <a href="{{ route('house.chat', $house->house_id) }}" class="btn-secondary btn-sm">
                                    Написать
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@vite(['resources/js/pages/orders-filters.js'])



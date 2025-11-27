@php
    $orders = $orders ?? collect();
    $currentUser = auth()->user();
    $user = $user ?? null;
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
                        // Определяем роль пользователя профиля относительно заказа
                        // Если пользователь профиля - владелец дома, то это заказ покупателя (owner)
                        // Если пользователь профиля - заказчик, то это его заказ (customer)
                        $isOwnerOfHouse = $user && $house && $user->user_id == $house->user_id;
                        $isCustomer = $user && $order->customer_id == $user->user_id;
                        
                        // Определяем роль пользователя профиля относительно заказа
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
                        
                        // Получаем фото дома
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
                                <div class="orders-compact-title">Заказ #{{ $order->order_id }}</div>
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

<style>
    .orders-compact-card {
        padding: 16px 18px;
        position: relative;
        border-left: 5px solid transparent;
    }
    
    /* Синяя полоска - мы покупали (customer) */
    .order-role-customer {
        border-left-color: #3b82f6;
    }
    
    /* Зеленая полоска - у нас покупали (owner) */
    .order-role-owner {
        border-left-color: #10b981;
    }
    
    .orders-compact-content {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .orders-compact-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .orders-compact-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
    }
    
    .orders-compact-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .orders-compact-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .orders-compact-label {
        color: #6b7280;
        font-weight: 500;
        min-width: 80px;
    }
    
    .orders-compact-text {
        color: #111827;
    }
    
    .orders-compact-link {
        color: #4f46e5;
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .orders-compact-link:hover {
        color: #4338ca;
        text-decoration: underline;
    }
    
    .orders-compact-actions {
        display: flex;
        gap: 8px;
        padding-top: 8px;
        border-top: 1px solid #f3f4f6;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    .order-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
        white-space: nowrap;
        letter-spacing: 0.3px;
    }
    
    /* Рассмотрение - желтый */
    .order-status-рассмотрение,
    .order-status-pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 1px solid #fbbf24;
        box-shadow: 0 2px 4px rgba(146, 64, 14, 0.1);
    }
    
    /* Обработка - синий */
    .order-status-обработка,
    .order-status-processing {
        background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
        color: #1e40af;
        border: 1px solid #3b82f6;
        box-shadow: 0 2px 4px rgba(30, 64, 175, 0.1);
    }
    
    /* Завершено - зеленый */
    .order-status-завершено,
    .order-status-completed {
        background: linear-gradient(135deg, #d1fae5 0%, #6ee7b7 100%);
        color: #065f46;
        border: 1px solid #10b981;
        box-shadow: 0 2px 4px rgba(6, 95, 70, 0.1);
    }
    
    /* Отменено - красный */
    .order-status-отменено,
    .order-status-cancelled {
        background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%);
        color: #991b1b;
        border: 1px solid #ef4444;
        box-shadow: 0 2px 4px rgba(153, 27, 27, 0.1);
    }
    
    /* Возврат - розовый */
    .order-status-возврат,
    .order-status-refund {
        background: linear-gradient(135deg, #fce7f3 0%, #f9a8d4 100%);
        color: #9f1239;
        border: 1px solid #ec4899;
        box-shadow: 0 2px 4px rgba(159, 18, 57, 0.1);
    }
    
    .orders-houses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 16px;
    }
    
    @media (max-width: 768px) {
        .orders-houses-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Стили для фильтров */
    .orders-filters-section {
        margin-bottom: 12px;
    }
    
    .orders-filters-card {
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 12px 16px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    
    .orders-filters-content {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .orders-filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .orders-filter-group-row {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-start;
    }
    
    .orders-filter-group-row .orders-filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .orders-filter-label {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 2px;
    }
    
    .orders-filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .orders-filter-checkbox {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }
    
    .orders-filter-checkbox input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }
    
    .orders-filter-checkbox-label {
        padding: 5px 10px;
        border-radius: 6px;
        border: 2px solid #d1d5db;
        background: #ffffff;
        font-size: 12px;
        font-weight: 500;
        color: #4b5563;
        cursor: pointer;
        transition: background-color 0.1s, border-color 0.1s, color 0.1s, box-shadow 0.1s;
        font-family: inherit;
        display: inline-block;
        white-space: nowrap;
        position: relative;
        box-sizing: border-box;
        min-width: 0;
        flex-shrink: 0;
        letter-spacing: 0.01em;
    }
    
    .orders-filter-checkbox:hover .orders-filter-checkbox-label {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
    
    .orders-filter-checkbox input[type="checkbox"]:checked + .orders-filter-checkbox-label {
        background: #ffffff;
        border: 2px solid transparent;
        color: #3b82f6;
        box-shadow: 0 1px 3px rgba(59, 130, 246, 0.2);
        font-weight: 600;
        letter-spacing: -0.01em;
    }
    
    .orders-filter-checkbox input[type="checkbox"]:checked + .orders-filter-checkbox-label::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 6px;
        padding: 2px;
        background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #2563eb 100%);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        z-index: -1;
    }
    
    .orders-filter-checkbox input[type="checkbox"]:checked + .orders-filter-checkbox-label:hover {
        background: #f8fafc;
        color: #2563eb;
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
    }
    
    .orders-filter-checkbox input[type="checkbox"]:checked + .orders-filter-checkbox-label:hover::before {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    }
    
    /* Старые стили для обратной совместимости */
    .orders-filter-btn {
        padding: 6px 12px;
        border-radius: 6px;
        border: 2px solid #d1d5db;
        background: #ffffff;
        font-size: 13px;
        font-weight: 500;
        color: #4b5563;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
        position: relative;
        box-sizing: border-box;
    }
    
    .orders-filter-btn:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
    
    .orders-filter-btn.active {
        background: #ffffff;
        border: 2px solid transparent;
        color: #3b82f6;
        box-shadow: 0 1px 3px rgba(59, 130, 246, 0.2);
        font-weight: 600;
    }
    
    .orders-filter-btn.active::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 6px;
        padding: 2px;
        background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #2563eb 100%);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    
    .orders-filter-btn.active:hover {
        background: #f8fafc;
        color: #2563eb;
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
    }
    
    .orders-filter-btn.active:hover::before {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    }
    
    .orders-filter-input-wrapper {
        position: relative;
        width: 100%;
    }
    
    .orders-filter-input {
        width: 100%;
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        font-size: 13px;
        font-weight: 500;
        color: #4b5563;
        transition: all 0.2s;
        font-family: inherit;
        box-sizing: border-box;
    }
    
    .orders-filter-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .orders-autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 4px;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    
    .orders-autocomplete-dropdown.show {
        display: block;
    }
    
    .orders-autocomplete-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 13px;
        color: #4b5563;
        transition: background 0.15s;
    }
    
    .orders-autocomplete-item:hover,
    .orders-autocomplete-item.highlighted {
        background: #f3f4f6;
    }
    
    .orders-autocomplete-item:first-child {
        border-radius: 6px 6px 0 0;
    }
    
    .orders-autocomplete-item:last-child {
        border-radius: 0 0 6px 6px;
    }
    
    .orders-empty-message {
        grid-column: 1 / -1;
        padding: 24px;
        text-align: center;
        color: #6b7280;
        font-size: 14px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px dashed #e5e7eb;
    }
    
    @media (max-width: 768px) {
        .orders-filters-card {
            padding: 10px 12px;
        }
        
        .orders-filters-content {
            flex-direction: column;
            gap: 12px;
        }
        
        .orders-filter-buttons {
            flex-direction: column;
        }
        
        .orders-filter-btn,
        .orders-filter-checkbox {
            width: 100%;
        }
        
        .orders-filter-checkbox-label {
            width: 100%;
            text-align: center;
        }
        
        .orders-filter-group {
            width: 100%;
        }
        
        .orders-filter-input-wrapper {
            width: 100%;
        }
    }
</style>

<script>
    // Глобальная функция для инициализации фильтров заказов
    window.initOrdersFilters = function(container) {
        // Если контейнер не указан, используем document
        container = container || document;
        
        const ordersGrid = container.getElementById('orders-houses-grid');
        if (!ordersGrid) {
            console.log('Orders grid not found');
            return;
        }
        
        const roleCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-role]');
        const statusCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-status]');
        const orderCards = container.querySelectorAll('.orders-compact-card');
        
        if (!roleCheckboxes.length || !statusCheckboxes.length) {
            console.log('Filter checkboxes not found');
            return;
        }
        
        if (!orderCards.length) {
            console.log('Order cards not found');
            return;
        }
        
        console.log('Initializing filters:', roleCheckboxes.length, 'role checkboxes,', statusCheckboxes.length, 'status checkboxes,', orderCards.length, 'cards');
        
        // Функция для получения выбранных фильтров
        function getSelectedFilters() {
            const selectedRoles = [];
            const selectedStatuses = [];
            
            // Получаем актуальные чекбоксы каждый раз
            const currentRoleCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-role]');
            const currentStatusCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-status]');
            
            currentRoleCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedRoles.push(checkbox.dataset.filterRole);
                }
            });
            
            currentStatusCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedStatuses.push(checkbox.dataset.filterStatus);
                }
            });
            
            return {
                roles: selectedRoles.length > 0 ? selectedRoles : ['customer', 'owner'], // Если ничего не выбрано, показываем все
                statuses: selectedStatuses.length > 0 ? selectedStatuses : ['рассмотрение', 'обработка', 'завершено', 'отменено', 'возврат'] // Если ничего не выбрано, показываем все
            };
        }
        
        // Функция для фильтрации заказов
        function filterOrders() {
            const cards = container.querySelectorAll('.orders-compact-card');
            const grid = container.getElementById('orders-houses-grid');
            if (!grid) return;
            
            const filters = getSelectedFilters();
            
            // Получаем значения из полей ввода
            const nameInput = container.querySelector('#filter-name-input');
            const addressInput = container.querySelector('#filter-address-input');
            const nameFilter = (nameInput ? nameInput.value.trim().toLowerCase() : '');
            const addressFilter = (addressInput ? addressInput.value.trim().toLowerCase() : '');
            
            let visibleCount = 0;
            
            console.log('Filtering with:', filters, 'name:', nameFilter, 'address:', addressFilter);
            
            cards.forEach(card => {
                const cardRole = (card.dataset.orderRole || '').toLowerCase();
                const cardStatus = (card.dataset.orderStatus || '').toLowerCase();
                const customerName = (card.dataset.customerName || '').toLowerCase();
                const ownerName = (card.dataset.ownerName || '').toLowerCase();
                const houseAddress = (card.dataset.houseAddress || '').toLowerCase();
                
                // Проверяем фильтр по роли
                const roleMatch = filters.roles.includes(cardRole);
                
                // Проверяем фильтр по статусу
                const statusMatch = filters.statuses.includes(cardStatus);
                
                // Проверяем фильтр по имени (ищем в имени заказчика или владельца)
                const nameMatch = !nameFilter || 
                    customerName.includes(nameFilter) || 
                    ownerName.includes(nameFilter);
                
                // Проверяем фильтр по адресу
                const addressMatch = !addressFilter || 
                    houseAddress.includes(addressFilter);
                
                // Показываем карточку только если все фильтры совпадают
                if (roleMatch && statusMatch && nameMatch && addressMatch) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Показываем сообщение, если нет видимых заказов
            let emptyMessage = grid.querySelector('.orders-empty-message');
            if (visibleCount === 0) {
                if (!emptyMessage) {
                    emptyMessage = document.createElement('div');
                    emptyMessage.className = 'orders-empty-message';
                    emptyMessage.textContent = 'Заказы с выбранными фильтрами не найдены.';
                    grid.appendChild(emptyMessage);
                }
            } else {
                if (emptyMessage) {
                    emptyMessage.remove();
                }
            }
        }
        
        // Используем делегирование событий для надежности
        const filtersContainer = container.querySelector('.orders-filters-card') || container;
        
        filtersContainer.addEventListener('change', function(e) {
            const checkbox = e.target;
            if (checkbox.type === 'checkbox' && (checkbox.dataset.filterRole || checkbox.dataset.filterStatus)) {
                // Немедленная реакция без задержки
                filterOrders();
            }
        }, { passive: true });
        
        // Также добавляем обработчики напрямую для совместимости
        roleCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Немедленная реакция без задержки
                filterOrders();
            }, { passive: true });
        });
        
        statusCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Немедленная реакция без задержки
                filterOrders();
            }, { passive: true });
        });
        
        // Инициализируем автодополнение для полей ввода, передавая функцию фильтрации
        initAutocomplete(container, filterOrders);
        
        // Инициализируем фильтрацию при загрузке
        filterOrders();
    };
    
    // Функция для инициализации автодополнения
    function initAutocomplete(container, filterOrdersFunc) {
        const nameInput = container.querySelector('#filter-name-input');
        const addressInput = container.querySelector('#filter-address-input');
        const nameDropdown = container.querySelector('#name-autocomplete');
        const addressDropdown = container.querySelector('#address-autocomplete');
        
        if (!nameInput || !addressInput) return;
        
        // Собираем все уникальные имена и адреса из заказов
        const orderCards = container.querySelectorAll('.orders-compact-card');
        const namesSet = new Set();
        const addressesSet = new Set();
        
        orderCards.forEach(card => {
            // Используем оригинальные значения с сохранением регистра
            const customerName = (card.dataset.customerNameOriginal || card.dataset.customerName || '').trim();
            const ownerName = (card.dataset.ownerNameOriginal || card.dataset.ownerName || '').trim();
            const address = (card.dataset.houseAddressOriginal || card.dataset.houseAddress || '').trim();
            
            if (customerName) namesSet.add(customerName);
            if (ownerName) namesSet.add(ownerName);
            if (address) addressesSet.add(address);
        });
        
        const names = Array.from(namesSet).sort();
        const addresses = Array.from(addressesSet).sort();
        
        // Функция для фильтрации и отображения вариантов
        function showSuggestions(input, dropdown, items, filterValue) {
            const query = filterValue.toLowerCase().trim();
            
            if (query.length === 0) {
                dropdown.classList.remove('show');
                return;
            }
            
            // Фильтруем, но сохраняем оригинальные значения с регистром
            const filtered = items.filter(item => 
                item.toLowerCase().includes(query)
            ).slice(0, 10); // Ограничиваем до 10 вариантов
            
            if (filtered.length === 0) {
                dropdown.classList.remove('show');
                return;
            }
            
            dropdown.innerHTML = '';
            filtered.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'orders-autocomplete-item';
                // Используем оригинальное значение с сохранением регистра
                itemEl.textContent = item;
                itemEl.addEventListener('click', () => {
                    // Сохраняем оригинальное значение с регистром
                    input.value = item;
                    dropdown.classList.remove('show');
                    filterOrders();
                });
                dropdown.appendChild(itemEl);
            });
            
            dropdown.classList.add('show');
        }
        
        // Обработчики для поля имени
        let nameHighlightIndex = -1;
        nameInput.addEventListener('input', function() {
            nameHighlightIndex = -1;
            showSuggestions(nameInput, nameDropdown, names, this.value);
            // Немедленно запускаем фильтрацию при вводе
            if (filterOrdersFunc) {
                filterOrdersFunc();
            }
        });
        
        nameInput.addEventListener('keydown', function(e) {
            const items = nameDropdown.querySelectorAll('.orders-autocomplete-item');
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                nameHighlightIndex = Math.min(nameHighlightIndex + 1, items.length - 1);
                items.forEach((item, idx) => {
                    item.classList.toggle('highlighted', idx === nameHighlightIndex);
                });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                nameHighlightIndex = Math.max(nameHighlightIndex - 1, -1);
                items.forEach((item, idx) => {
                    item.classList.toggle('highlighted', idx === nameHighlightIndex);
                });
            } else if (e.key === 'Enter' && nameHighlightIndex >= 0) {
                e.preventDefault();
                items[nameHighlightIndex].click();
            } else if (e.key === 'Escape') {
                nameDropdown.classList.remove('show');
            }
        });
        
        // Обработчики для поля адреса
        let addressHighlightIndex = -1;
        addressInput.addEventListener('input', function() {
            addressHighlightIndex = -1;
            showSuggestions(addressInput, addressDropdown, addresses, this.value);
            // Немедленно запускаем фильтрацию при вводе
            if (filterOrdersFunc) {
                filterOrdersFunc();
            }
        });
        
        addressInput.addEventListener('keydown', function(e) {
            const items = addressDropdown.querySelectorAll('.orders-autocomplete-item');
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                addressHighlightIndex = Math.min(addressHighlightIndex + 1, items.length - 1);
                items.forEach((item, idx) => {
                    item.classList.toggle('highlighted', idx === addressHighlightIndex);
                });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                addressHighlightIndex = Math.max(addressHighlightIndex - 1, -1);
                items.forEach((item, idx) => {
                    item.classList.toggle('highlighted', idx === addressHighlightIndex);
                });
            } else if (e.key === 'Enter' && addressHighlightIndex >= 0) {
                e.preventDefault();
                items[addressHighlightIndex].click();
            } else if (e.key === 'Escape') {
                addressDropdown.classList.remove('show');
            }
        });
        
        // Закрываем выпадающие меню при клике вне их
        document.addEventListener('click', function(e) {
            if (!nameInput.contains(e.target) && !nameDropdown.contains(e.target)) {
                nameDropdown.classList.remove('show');
            }
            if (!addressInput.contains(e.target) && !addressDropdown.contains(e.target)) {
                addressDropdown.classList.remove('show');
            }
        });
    }
    
    // Пытаемся инициализировать сразу
    (function() {
        function tryInit() {
            const grid = document.getElementById('orders-houses-grid');
            if (grid && window.initOrdersFilters) {
                window.initOrdersFilters();
            }
        }
        
        // Пробуем сразу
        tryInit();
        
        // Пробуем после загрузки DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(tryInit, 100);
            });
        } else {
            setTimeout(tryInit, 200);
        }
        
        // Пробуем еще раз через некоторое время (для AJAX)
        setTimeout(tryInit, 500);
        setTimeout(tryInit, 1000);
    })();
</script>



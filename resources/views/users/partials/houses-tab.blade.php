@php
    $houses = $user->house ?? collect();
    $currentUser = auth()->user();
    $canCreateHouse = $currentUser && $currentUser->canCreateHouse();
@endphp

<div class="settings-tab-content">
    @if($houses->isEmpty())
        <div class="settings-section">
            @if(isset($isOwner) && $isOwner && $canCreateHouse)
                <div class="orders-search-container">
                    <a href="{{ route('houses.create') }}" class="btn-primary orders-create-btn" style="margin-left: auto;">
                        Создать
                    </a>
                </div>
            @endif
            <div class="settings-section-card">
                <div class="profile-empty">
                    У вас пока нет домов.
                </div>
            </div>
        </div>
    @else
        @php
            // Собираем уникальные типы аренды и типы домов
            $rentTypes = collect();
            $houseTypes = collect();
            
            foreach($houses as $house) {
                if($house->rent_type && !$rentTypes->contains('rent_type_id', $house->rent_type->rent_type_id)) {
                    $rentTypes->push($house->rent_type);
                }
                if($house->house_type && !$houseTypes->contains('house_type_id', $house->house_type->house_type_id)) {
                    $houseTypes->push($house->house_type);
                }
            }
        @endphp
        
        {{-- Панель фильтров --}}
        <div class="orders-filters-section">
            <div class="orders-filters-card">
                <div class="orders-filters-content">
                    <div class="orders-filter-group-row">
                        @if($rentTypes->isNotEmpty())
                        <div class="orders-filter-group">
                            <label class="orders-filter-label">Тип аренды:</label>
                            <div class="orders-filter-buttons">
                                @foreach($rentTypes as $rentType)
                                    <label class="orders-filter-checkbox">
                                        <input type="checkbox" data-filter-rent-type="{{ mb_strtolower($rentType->name ?? '', 'UTF-8') }}" checked>
                                        <span class="orders-filter-checkbox-label">{{ $rentType->name ?? '' }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if($houseTypes->isNotEmpty())
                        <div class="orders-filter-group">
                            <label class="orders-filter-label">Тип дома:</label>
                            <div class="orders-filter-buttons">
                                @foreach($houseTypes as $houseType)
                                    <label class="orders-filter-checkbox">
                                        <input type="checkbox" data-filter-house-type="{{ mb_strtolower($houseType->name ?? '', 'UTF-8') }}" checked>
                                        <span class="orders-filter-checkbox-label">{{ $houseType->name ?? '' }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="orders-filter-group-row">
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
            @if(isset($isOwner) && $isOwner && $canCreateHouse)
                <div class="orders-search-container" style="justify-content: flex-end; margin-bottom: 12px;">
                    <a href="{{ route('houses.create') }}" class="btn-primary orders-create-btn">
                        Создать
                    </a>
                </div>
            @endif
            <div class="orders-houses-grid" id="orders-houses-grid">
                @foreach($houses as $house)
                    @php
                        $searchParts = array_filter([
                            $house->adress ?? '',
                            (string)($house->house_id ?? ''),
                            optional($house->rent_type)->name ?? '',
                            optional($house->house_type)->name ?? '',
                            $house->area ? (string)$house->area : '',
                            $house->price_id ? (string)$house->price_id : '',
                        ], function($value) {
                            return $value !== '' && $value !== null;
                        });
                        $searchText = mb_strtolower(implode(' ', $searchParts), 'UTF-8');
                        // Нормализуем пробелы - заменяем множественные пробелы на одинарные
                        $searchText = preg_replace('/\s+/u', ' ', $searchText);
                        $searchText = trim($searchText);
                        
                        $rentTypeName = $house->rent_type ? mb_strtolower($house->rent_type->name ?? '', 'UTF-8') : '';
                        $houseTypeName = $house->house_type ? mb_strtolower($house->house_type->name ?? '', 'UTF-8') : '';
                        $houseAddress = $house->adress ?? '';
                    @endphp
                    <div 
                        class="orders-house-card" 
                        data-search-text="{{ e($searchText) }}"
                        data-rent-type="{{ $rentTypeName }}"
                        data-rent-type-original="{{ htmlspecialchars($house->rent_type->name ?? '', ENT_QUOTES, 'UTF-8') }}"
                        data-house-type="{{ $houseTypeName }}"
                        data-house-type-original="{{ htmlspecialchars($house->house_type->name ?? '', ENT_QUOTES, 'UTF-8') }}"
                        data-house-address="{{ mb_strtolower($houseAddress, 'UTF-8') }}"
                        data-house-address-original="{{ htmlspecialchars($houseAddress, ENT_QUOTES, 'UTF-8') }}"
                    >
                        @php
                            $photoPayload = $house->photo
                                ->filter(fn($photo) => !empty($photo->path))
                                ->map(fn($photo) => [
                                    'path' => $photo->path,
                                    'name' => $photo->name,
                                ])
                                ->values();
                        @endphp

                        <div class="orders-house-image-wrapper">
                            <div
                                class="orders-house-image"
                                data-house-photos='@json($photoPayload)'
                                data-empty-text="Нет фотографий"
                            >
                                @if($photoPayload->isNotEmpty())
                                    <img
                                        src="{{ asset('storage/' . $photoPayload->first()['path']) }}"
                                        alt="Фото дома #{{ $house->house_id }}"
                                    >
                                @else
                                    <div class="orders-house-image-placeholder">🏠</div>
                                @endif
                            </div>
                            @if($house->price_id)
                            <div class="orders-house-price-badge">
                                {{ number_format($house->price_id, 0, ',', ' ') }} ₽
                            </div>
                            @endif
                        </div>

                        <div class="orders-house-content">
                            <div class="orders-house-title">{{ $house->adress ?? 'Дом #' . $house->house_id }}</div>
                            
                            <div class="orders-house-badges">
                                @if($house->area)
                                    <span class="house-badge">{{ number_format($house->area, 0, ',', ' ') }} м²</span>
                                @endif
                                @if($house->rent_type)
                                    <span class="house-badge">{{ $house->rent_type->name }}</span>
                                @endif
                                @if($house->house_type)
                                    <span class="house-badge">{{ $house->house_type->name }}</span>
                                @endif
                            </div>

                            <div class="orders-house-actions">
                                <a href="{{ route('houses.show', $house->house_id) }}" class="btn-primary">
                                    Просмотр
                                </a>
                                @php
                                    $canEditHouse = ($isOwner ?? false) || ($currentUser && $currentUser->isAdmin());
                                @endphp
                                @if($canEditHouse)
                                    <a href="{{ route('houses.edit', $house->house_id) }}" class="btn-secondary">
                                        Редактировать
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="orders-no-results" class="settings-section-card" style="display: none;">
                <div class="profile-empty">
                    По вашему запросу ничего не найдено.
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Глобальная функция для инициализации фильтров домов
    window.initHousesFilters = function(container) {
        container = container || document;
        
        const housesGrid = container.getElementById('orders-houses-grid');
        if (!housesGrid) {
            console.log('Houses grid not found');
            return;
        }
        
        const rentTypeCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-rent-type]');
        const houseTypeCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-house-type]');
        const houseCards = container.querySelectorAll('.orders-house-card');
        
        if (!houseCards.length) {
            console.log('House cards not found');
            return;
        }
        
        console.log('Initializing houses filters:', rentTypeCheckboxes.length, 'rent type checkboxes,', houseTypeCheckboxes.length, 'house type checkboxes,', houseCards.length, 'cards');
        
        // Функция для получения выбранных фильтров
        function getSelectedFilters() {
            const selectedRentTypes = [];
            const selectedHouseTypes = [];
            
            rentTypeCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedRentTypes.push(checkbox.dataset.filterRentType);
                }
            });
            
            houseTypeCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedHouseTypes.push(checkbox.dataset.filterHouseType);
                }
            });
            
            return {
                rentTypes: selectedRentTypes.length > 0 ? selectedRentTypes : Array.from(rentTypeCheckboxes).map(cb => cb.dataset.filterRentType),
                houseTypes: selectedHouseTypes.length > 0 ? selectedHouseTypes : Array.from(houseTypeCheckboxes).map(cb => cb.dataset.filterHouseType)
            };
        }
        
        // Функция для фильтрации домов
        function filterHouses() {
            const cards = container.querySelectorAll('.orders-house-card');
            const grid = container.getElementById('orders-houses-grid');
            if (!grid) return;
            
            const filters = getSelectedFilters();
            
            // Получаем значение из поля ввода адреса
            const addressInput = container.querySelector('#filter-address-input');
            const addressFilter = (addressInput ? addressInput.value.trim().toLowerCase() : '');
            
            let visibleCount = 0;
            
            console.log('Filtering houses with:', filters, 'address:', addressFilter);
            
            cards.forEach(card => {
                const cardRentType = (card.dataset.rentType || '').toLowerCase();
                const cardHouseType = (card.dataset.houseType || '').toLowerCase();
                const houseAddress = (card.dataset.houseAddress || '').toLowerCase();
                
                // Проверяем фильтр по типу аренды
                const rentTypeMatch = filters.rentTypes.length === 0 || filters.rentTypes.includes(cardRentType);
                
                // Проверяем фильтр по типу дома
                const houseTypeMatch = filters.houseTypes.length === 0 || filters.houseTypes.includes(cardHouseType);
                
                // Проверяем фильтр по адресу
                const addressMatch = !addressFilter || houseAddress.includes(addressFilter);
                
                // Показываем карточку только если все фильтры совпадают
                if (rentTypeMatch && houseTypeMatch && addressMatch) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Показываем сообщение, если нет видимых домов
            let noResults = container.querySelector('#orders-no-results');
            if (visibleCount === 0) {
                if (noResults) {
                    noResults.style.display = '';
                }
            } else {
                if (noResults) {
                    noResults.style.display = 'none';
                }
            }
        }
        
        // Используем делегирование событий для надежности
        const filtersContainer = container.querySelector('.orders-filters-card') || container;
        
        filtersContainer.addEventListener('change', function(e) {
            const checkbox = e.target;
            if (checkbox.type === 'checkbox' && (checkbox.dataset.filterRentType || checkbox.dataset.filterHouseType)) {
                filterHouses();
            }
        }, { passive: true });
        
        // Также добавляем обработчики напрямую для совместимости
        rentTypeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                filterHouses();
            }, { passive: true });
        });
        
        houseTypeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                filterHouses();
            }, { passive: true });
        });
        
        // Инициализируем автодополнение для поля адреса
        initHousesAutocomplete(container, filterHouses);
        
        // Инициализируем фильтрацию при загрузке
        filterHouses();
    };
    
    // Функция для инициализации автодополнения для домов
    function initHousesAutocomplete(container, filterHousesFunc) {
        const addressInput = container.querySelector('#filter-address-input');
        const addressDropdown = container.querySelector('#address-autocomplete');
        
        if (!addressInput) return;
        
        // Собираем все уникальные адреса из домов
        const houseCards = container.querySelectorAll('.orders-house-card');
        const addressesSet = new Set();
        
        houseCards.forEach(card => {
            const address = (card.dataset.houseAddressOriginal || card.dataset.houseAddress || '').trim();
            if (address) addressesSet.add(address);
        });
        
        const addresses = Array.from(addressesSet).sort();
        
        // Функция для фильтрации и отображения вариантов
        function showSuggestions(input, dropdown, items, filterValue) {
            const query = filterValue.toLowerCase().trim();
            
            if (query.length === 0) {
                dropdown.classList.remove('show');
                return;
            }
            
            const filtered = items.filter(item => 
                item.toLowerCase().includes(query)
            ).slice(0, 10);
            
            if (filtered.length === 0) {
                dropdown.classList.remove('show');
                return;
            }
            
            dropdown.innerHTML = '';
            filtered.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'orders-autocomplete-item';
                itemEl.textContent = item;
                itemEl.addEventListener('click', () => {
                    input.value = item;
                    dropdown.classList.remove('show');
                    if (filterHousesFunc) {
                        filterHousesFunc();
                    }
                });
                dropdown.appendChild(itemEl);
            });
            
            dropdown.classList.add('show');
        }
        
        // Обработчики для поля адреса
        let addressHighlightIndex = -1;
        addressInput.addEventListener('input', function() {
            addressHighlightIndex = -1;
            showSuggestions(addressInput, addressDropdown, addresses, this.value);
            // Немедленно запускаем фильтрацию при вводе
            if (filterHousesFunc) {
                filterHousesFunc();
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
        
        // Закрываем выпадающее меню при клике вне его
        document.addEventListener('click', function(e) {
            if (!addressInput.contains(e.target) && !addressDropdown.contains(e.target)) {
                addressDropdown.classList.remove('show');
            }
        });
    }
    
    // Пытаемся инициализировать сразу
    (function() {
        function tryInit() {
            const grid = document.getElementById('orders-houses-grid');
            if (grid && window.initHousesFilters) {
                window.initHousesFilters();
            }
        }
        
        tryInit();
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(tryInit, 100);
            });
        } else {
            setTimeout(tryInit, 200);
        }
        
        setTimeout(tryInit, 500);
        setTimeout(tryInit, 1000);
    })();
</script>

<style>
    /* Стили для фильтров домов (аналогично фильтрам заказов) */
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
    
    .orders-filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
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
    
    .orders-search-container {
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .orders-search-input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: #ffffff;
        color: #111827;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    
    .orders-search-input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .orders-search-input::placeholder {
        color: #9ca3af;
    }
    
    .orders-create-btn {
        white-space: nowrap;
        flex-shrink: 0;
        padding: 12px 16px;
        height: auto;
        box-sizing: border-box;
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
    
    /* Сетка домов - 3 колонки */
    .orders-houses-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    
    @media (max-width: 1200px) {
        .orders-houses-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .orders-houses-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Компактные карточки домов */
    .orders-house-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.2s ease;
    }
    
    .orders-house-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.15);
        border-color: #d1d5db;
    }
    
    .orders-house-image-wrapper {
        position: relative;
        width: 100%;
        height: 180px;
        overflow: hidden;
        background: #f3f4f6;
    }
    
    .orders-house-image {
        width: 100%;
        height: 100%;
        position: relative;
    }
    
    .orders-house-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .orders-house-card:hover .orders-house-image img {
        transform: scale(1.08);
    }
    
    .orders-house-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        background: #f3f4f6;
        color: #9ca3af;
    }
    
    .orders-house-image .photo-carousel {
        margin-top: 0;
    }
    
    .orders-house-image .photos-viewport {
        height: 180px;
        border-radius: 0;
    }
    
    .orders-house-price-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(79, 70, 229, 0.95);
        color: #ffffff;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .orders-house-content {
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex: 1;
    }
    
    .orders-house-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        line-height: 1.4;
        margin: 0;
    }
    
    .orders-house-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .house-badge {
        display: inline-block;
        padding: 4px 10px;
        background: #f3f4f6;
        color: #374151;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #e5e7eb;
    }
    
    .orders-house-actions {
        display: flex;
        gap: 8px;
        margin-top: 4px;
    }
    
    .orders-house-actions .btn-primary,
    .orders-house-actions .btn-secondary {
        flex: 1;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 500;
        text-align: center;
        border-radius: 8px;
    }
</style>


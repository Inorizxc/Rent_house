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
        <div class="settings-section">
            <div class="orders-search-container">
                <input 
                    type="text" 
                    id="orders-search-input" 
                    class="orders-search-input" 
                    placeholder="Поиск по адресу, ID, типу аренды, типу дома..."
                    autocomplete="off"
                >
                @if(isset($isOwner) && $isOwner && $canCreateHouse)
                    <a href="{{ route('houses.create') }}" class="btn-primary orders-create-btn">
                        Создать
                    </a>
                @endif
            </div>
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
                    @endphp
                    <div 
                        class="orders-house-card" 
                        data-search-text="{{ e($searchText) }}"
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
    (function() {
        // Используем флаг для предотвращения множественной инициализации
        if (window.ordersSearchInitialized) {
            return;
        }
        
        function initOrdersSearch() {
            const searchInput = document.getElementById('orders-search-input');
            const housesGrid = document.getElementById('orders-houses-grid');
            const noResults = document.getElementById('orders-no-results');
            
            if (!searchInput || !housesGrid) return;
            
            // Проверяем, не инициализирован ли уже обработчик
            if (searchInput.dataset.searchInitialized === 'true') {
                return;
            }
            
            // Создаем обработчик
            const searchHandler = function() {
                let searchText = this.value || '';
                
                // Нормализуем поисковый запрос: убираем пробелы в начале и конце, приводим к нижнему регистру
                searchText = searchText.trim().toLowerCase();
                
                // Нормализуем множественные пробелы внутри строки
                searchText = searchText.replace(/\s+/g, ' ');
                
                const houseCards = housesGrid.querySelectorAll('.orders-house-card');
                let visibleCount = 0;
                
                houseCards.forEach(function(card) {
                    // Получаем данные из атрибута
                    let searchDataAttr = card.getAttribute('data-search-text');
                    if (!searchDataAttr) {
                        // Если нет данных для поиска, показываем карточку только если поиск пустой
                        if (!searchText) {
                            card.style.display = '';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                        return;
                    }
                    
                    // Нормализуем данные для поиска
                    let searchData = searchDataAttr.trim().toLowerCase();
                    searchData = searchData.replace(/\s+/g, ' ');
                    
                    // Проверяем, содержит ли строка поисковый запрос
                    const matches = !searchText || searchData.includes(searchText);
                    
                    if (matches) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Показываем/скрываем сообщение "ничего не найдено"
                if (visibleCount === 0 && searchText) {
                    if (noResults) noResults.style.display = '';
                } else {
                    if (noResults) noResults.style.display = 'none';
                }
            };
            
            searchInput.addEventListener('input', searchHandler);
            searchInput.dataset.searchInitialized = 'true';
        }
        
        // Инициализируем при загрузке DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initOrdersSearch);
        } else {
            initOrdersSearch();
        }
        
        // Также инициализируем сразу, если элементы уже есть (для AJAX загрузки)
        setTimeout(initOrdersSearch, 100);
        
        window.ordersSearchInitialized = true;
    })();
</script>

<style>
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


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
                        class="settings-section-card orders-house-card" 
                        data-search-text="{{ e($searchText) }}"
                    >
                        <div class="orders-house-header">
                            <div class="orders-house-title">{{ $house->adress ?? 'Дом #' . $house->house_id }}</div>
                            <div class="orders-house-subtitle">Дом #{{ $house->house_id }}</div>
                        </div>

                        @php
                            $photoPayload = $house->photo
                                ->filter(fn($photo) => !empty($photo->path))
                                ->map(fn($photo) => [
                                    'path' => $photo->path,
                                    'name' => $photo->name,
                                ])
                                ->values();
                        @endphp

                        <div class="orders-house-photos">
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
                        </div>

                        <div class="settings-section-title">О доме</div>
                        <div class="orders-house-description">
                            <div class="description-row">
                                <div class="description-label">Адрес</div>
                                <div class="description-value">{{ $house->adress ?? '—' }}</div>
                            </div>
                            <div class="description-row">
                                <div class="description-label">Площадь</div>
                                <div class="description-value">
                                    {{ $house->area ? number_format($house->area, 0, ',', ' ') . ' м²' : '—' }}
                                </div>
                            </div>
                            <div class="description-row">
                                <div class="description-label">Тип аренды</div>
                                <div class="description-value">
                                    {{ optional($house->rent_type)->name ?? '—' }}
                                </div>
                            </div>
                            <div class="description-row">
                                <div class="description-label">Тип дома</div>
                                <div class="description-value">
                                    {{ optional($house->house_type)->name ?? '—' }}
                                </div>
                            </div>
                            <div class="description-row">
                                <div class="description-label">Координаты</div>
                                <div class="description-value">
                                    {{ $house->lat && $house->lng ? $house->lat . ', ' . $house->lng : '—' }}
                                </div>
                            </div>
                            <div class="description-row">
                                <div class="description-label">Стоимость</div>
                                <div class="description-value">
                                    @if($house->price_id)
                                        {{ number_format($house->price_id, 0, ',', ' ') }} ₽
                                    @else
                                        Не указана
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="orders-house-actions">
                            <a href="{{ route('houses.show', $house->house_id) }}" class="btn-primary">
                                Просмотр
                            </a>
                            <a href="{{ route('houses.edit', $house->house_id) }}" class="btn-secondary">
                                Редактировать
                            </a>
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
</style>


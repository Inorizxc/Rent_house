@extends('layout')

@section('title')
    Карта домов
@endsection

@section('style')
    :root {
        --header-height: 57px;
        --panel-radius: 12px;
        --panel-border: #e2e2e5;
        --panel-bg: #ffffff;
        --panel-bg-soft: #f8f9fa;
        --text-main: #1f2933;
        --text-muted: #6b7280;
        --accent-border: #d0d0d5;
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        --panel-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
        --panel-shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.12), 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        /* шрифт как в шапке */
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f6f6f7;
        overflow: hidden;
    }

    /* Карта — на всю страницу, под всем остальным */
    #map {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        z-index: 0;
    }

    /* Оверлей с панелями — ниже шапки */
    .overlay {
        position: fixed;
        top: calc(var(--header-height) - 2px); /* меньше отступ сверху */
        left: 0;
        width: 100vw;
        height: calc(100vh - var(--header-height) - 2px); /* учитываем уменьшенный верхний отступ */

        z-index: 1;

        display: flex;
        gap: 2px;          /* расстояние между панелями — меньше */
        padding: 0 2px;     /* маленькие боковые отступы */
        pointer-events: none;
    }

    .map-spacer {
        flex: 1;
        pointer-events: none;
    }

    /* Общий стиль панелей в стиле шапки */
    .panel {
        pointer-events: auto;
        display: flex;
        flex-direction: column;

        background: var(--panel-bg);
        border: 1px solid var(--panel-border);
        border-radius: var(--panel-radius);

        padding: 16px 16px;
        max-height: 100%;

        width: 280px;
        min-width: 260px;

        overflow-y: auto;
        overflow-x: hidden;
        box-shadow: var(--panel-shadow);
        backdrop-filter: blur(10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }


    #left-panel {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-color: #e9ecef;
    }

    #middle-panel {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-color: #e9ecef;
    }

    #right-panel {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-color: #e9ecef;
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
        position: relative;
    }

    .panel-header::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 40px;
        height: 2px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .panel-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
        white-space: nowrap;
        letter-spacing: -0.02em;
    }

    .panel-body {
        margin-top: 4px;
        font-size: 14px;
        color: var(--text-main);
    }

    /* Свернутая панель */
    .panel.collapsed {
        width: 44px;
        min-width: 44px;
        padding-inline: 8px;
    }

    .panel.collapsed .panel-title,
    .panel.collapsed .panel-body {
        display: none;
    }

    /* Кнопка сворачивания — в стиле шапки */
    .toggle-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--accent-border);
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;

        font-size: 14px;
        color: #4b5563;

        padding: 0;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .toggle-btn:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-color: #adb5bd;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .toggle-btn:active {
        transform: translateY(0) scale(1);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    /* Поля фильтров */
    .field {
        margin-bottom: 10px;
    }

    .field label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .field input,
    .field select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1.5px solid #d4d4dd;
        font-size: 13px;
        color: var(--text-main);
        background: #ffffff;
        outline: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .field input:focus,
    .field select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1), 0 2px 4px rgba(0, 0, 0, 0.1);
        background: #ffffff;
        transform: translateY(-1px);
    }

    .filters-row {
        display: flex;
        gap: 8px;
    }

    .filters-row .field {
        flex: 1;
    }

    /* Кнопка "Сбросить фильтры" — как кнопки в шапке */
    .btn-reset {
        margin-top: 8px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        border: 1.5px solid #e0e0e0;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        color: #374151;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .btn-reset:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-color: #adb5bd;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-reset:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    /* Список домов */
    .house-list {
        margin-top: 4px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .house-item {
        padding: 10px 12px;
        border-radius: 10px;
        cursor: pointer;
        border: 1.5px solid #e5e7eb;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 12px;
        color: var(--text-main);
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        position: relative;
        overflow: hidden;
    }

    .house-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background: var(--primary-gradient);
        opacity: 0;
        transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .house-item:hover {
        background: linear-gradient(135deg, #f1f5ff 0%, #e8f0ff 100%);
        border-color: #c7d2fe;
        transform: translateX(4px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.2);
    }

    .house-item:hover::before {
        opacity: 1;
    }

    .house-item.active {
        background: linear-gradient(135deg, #e0e7ff 0%, #d4dcf7 100%);
        border-color: #a5b4fc;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.25);
        transform: translateX(4px);
    }

    .house-item.active::before {
        opacity: 1;
    }

    .house-item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
        gap: 6px;
    }

    .house-item-id {
        font-size: 11px;
        font-weight: 700;
        color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        padding: 3px 8px;
        border-radius: 5px;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .house-item-address {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-main);
        line-height: 1.3;
        margin-bottom: 8px;
        display: flex;
        align-items: flex-start;
        gap: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .house-item-address::before {
        content: '📍';
        font-size: 12px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .house-item-details {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 0;
    }

    .house-item-detail {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: var(--text-muted);
        background: rgba(255, 255, 255, 0.6);
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        flex: 1;
        min-width: calc(50% - 3px);
    }

    .house-item-detail-icon {
        font-size: 11px;
        flex-shrink: 0;
    }

    .house-item-detail-label {
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        opacity: 0.7;
        margin-right: 2px;
        white-space: nowrap;
    }

    .house-item-detail-value {
        font-weight: 600;
        color: var(--text-main);
        font-size: 11px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .house-item-detail-value.type {
        color: #667eea;
    }

    .house-item-detail-value.area {
        color: #11998e;
    }

    .house-item-detail-value.price {
        color: #764ba2;
        font-weight: 700;
        font-size: 12px;
    }

    .house-item-empty {
        text-align: center;
        padding: 30px 20px;
        color: var(--text-muted);
        font-size: 14px;
        opacity: 0.7;
    }

    .info-label {
        margin-top: 10px;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        opacity: 0.8;
    }

    .info-label:first-child {
        margin-top: 0;
    }

    #house-info-card {
        font-size: 13px;
        line-height: 1.45;
        color: var(--text-main);
    }

    .info-item {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1.5px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .info-item-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 6px;
        opacity: 0.8;
    }

    .info-item-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        line-height: 1.4;
    }

    .info-item-value-address {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-main);
        line-height: 1.5;
    }

    .info-item-value-price {
        font-size: 18px;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.3;
    }

    .info-item-value-type {
        font-size: 15px;
        font-weight: 600;
        color: #667eea;
        line-height: 1.4;
    }

    .info-items-row {
        display: flex;
        gap: 10px;
        margin-top: 12px;
        margin-bottom: 10px;
    }

    .info-items-row .info-item {
        flex: 1;
        margin-bottom: 0;
    }

    .info-item-value-area {
        font-size: 15px;
        font-weight: 600;
        color: #11998e;
        line-height: 1.4;
    }

    .house-actions {
        margin-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .house-btn,
    .house-btn-secondary,
    .house-btn-order {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        gap: 8px;
    }

    .house-btn {
        background: var(--primary-gradient);
        border-color: transparent;
        color: #ffffff;
    }

    .house-btn:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a3f91 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }

    .house-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .house-btn-secondary {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-color: #e5e7eb;
        color: #111827;
    }

    .house-btn-secondary:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-color: #d1d5db;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .house-btn-order {
        background: var(--success-gradient);
        border-color: transparent;
        color: #ffffff;
        margin-bottom: 8px;
        font-size: 13px;
        padding: 10px 14px;
    }

    .house-btn-order:hover {
        background: linear-gradient(135deg, #0d7c75 0%, #2dd970 100%);
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
    }

    .house-btn-order:active {
        transform: translateY(0) scale(1);
        box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
    }

    .house-btn-order::before {
        content: '💬';
        font-size: 16px;
    }

    #house-info-empty {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.6;
        opacity: 0.7;
    }

    /* Убрать промо-надпись */
    .ymaps-2-1-79-map-copyrights-promo {
        display: none !important;
    }

    /* Убрать копирайт внизу справа */
    .ymaps-2-1-79-copyright__wrap {
        display: none !important;
    }


@endsection


@section('main_content')
    <div id="map"></div>
    
    <div class="overlay">
        <div class="panel" id="left-panel">
            <div class="panel-header">
                <span class="panel-title">Фильтры</span>
                <button class="toggle-btn" data-target="left-panel" data-side="left">❮</button>
            </div>
            <div class="panel-body">
                <div class="field">
                    <label for="search">Поиск по адресу</label>
                    <input type="text" id="search" placeholder="Введите адрес...">
                </div>

                <div class="field">
                    <label for="house_type">Тип дома</label>
                    <select id="house_type">
                        <option value="">Любой</option>
                    </select>
                </div>
<!-- Суета -->
                <div class="filters-row">
                    <div class="field">
                        <label for="price_min">Цена от</label>
                        <input type="number" id="price_min" placeholder="мин">
                    </div>
                    <div class="field">
                        <label for="price_max">Цена до</label>
                        <input type="number" id="price_max" placeholder="макс">
                    </div>
                </div>

                <div class="filters-row">
                    <div class="field">
                        <label for="area_min">Площадь от</label>
                        <input type="number" id="area_min" placeholder="мин">
                    </div>
                    <div class="field">
                        <label for="area_max">Площадь до</label>
                        <input type="number" id="area_max" placeholder="макс">
                    </div>
                </div>


                <button class="btn-reset" id="resetFilters">Сбросить фильтры</button>
            </div>
        </div>
        
        <div class="panel" id="middle-panel">
            <div class="panel-header">
                <span class="panel-title">Список домов</span>
                <button class="toggle-btn" data-target="middle-panel" data-side="left">❮</button>
            </div>
            <div class="panel-body">
                <div class="house-list" id="houseList"></div>
            </div>
        </div>

        <div class="map-spacer"></div>


        <div class="panel" id="right-panel">
            <div class="panel-header">
                <span class="panel-title">Информация о доме</span>
                <button class="toggle-btn" data-target="right-panel" data-side="right">❯</button>
            </div>
            <div class="panel-body">
                <div id="house-info">
                    <div id="house-info-empty">
                        Выберите дом в списке или на карте.
                    </div>
                </div>
            </div>
        </div>


    </div>
    
    <script src="https://api-maps.yandex.ru/2.1/?apikey=a2cd05de-c1e4-457b-8092-a8b0ebd9db10&lang=ru_RU" type="text/javascript"></script>
    <script>
        const houses = @json($houses);
        console.log(houses);

        ymaps.ready(function () {
            let defaultLat  = 51.533338;
            let defaultLng  = 46.034176;
            let defaultZoom = 10;

            const savedViewRaw = localStorage.getItem('housesMapView');

            if (savedViewRaw) {
                try {
                    const savedView = JSON.parse(savedViewRaw);
                    if (
                        typeof savedView.lat === 'number' &&
                        typeof savedView.lng === 'number' &&
                        typeof savedView.zoom === 'number'
                    ) {
                        defaultLat  = savedView.lat;
                        defaultLng  = savedView.lng;
                        defaultZoom = savedView.zoom;
                    }
                } catch (e) {
                    console.warn('Ошибка сохрания состояния карты:', e);
                }
            }


            const map = new ymaps.Map('map', {
                center: [defaultLat, defaultLng],
                zoom: defaultZoom,
                controls: []
            });
            

            function saveMapView() {
                const center = map.getCenter();
                const zoom   = map.getZoom();

                localStorage.setItem(
                    'housesMapView',
                    JSON.stringify({
                        lat: center[0],
                        lng: center[1],
                        zoom: zoom,
                    })
                );
            }
            map.events.add('boundschange', saveMapView);

            const markers = {};
            let activeHouseId = null;

            houses.forEach(el => {
                if (!el.lat || !el.lng) return;

                const lat = parseFloat(el.lat);
                const lng = parseFloat(el.lng);
                if (isNaN(lat) || isNaN(lng)) return;

                const placemark = new ymaps.Placemark(
                    [lat, lng],
                    {
                        balloonContentHeader: `Дом #${el.house_id}`,
                        balloonContentBody: el.adress ?? 'Адрес не указан',
                    },
                    {
                        preset: 'islands#blueIcon'
                    }
                );

                placemark.events.add('click', () => {
                    selectHouse(el.house_id, true);
                });

                markers[el.house_id] = placemark;
                map.geoObjects.add(placemark);
            });
            
            const searchInput   = document.getElementById('search');
            const houseTypeSel  = document.getElementById('house_type');
            const priceMinInput = document.getElementById('price_min');
            const priceMaxInput = document.getElementById('price_max');

            const areaMinInput = document.getElementById('area_min');
            const areaMaxInput = document.getElementById('area_max');

            const houseListDiv  = document.getElementById('houseList');
            const houseInfoDiv  = document.getElementById('house-info');
            const resetBtn      = document.getElementById('resetFilters');

            
            function fillSelectOptions() {
                const houseTypes = new Map();

                houses.forEach(el => {
                    if (el.house_type && el.house_type.name) {
                        houseTypes.set(el.house_type.house_type_id, el.house_type.name);
                    }
                });

                houseTypes.forEach((name, id) => {
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = name;
                    houseTypeSel.appendChild(opt);
                });
            }


            fillSelectOptions();

            function getFilteredHouses() {
                const q        = searchInput.value.trim().toLowerCase();
                const hType    = houseTypeSel.value;
                const priceMin = priceMinInput.value ? parseFloat(priceMinInput.value) : null;
                const priceMax = priceMaxInput.value ? parseFloat(priceMaxInput.value) : null;
                const areaMin = areaMinInput.value ? parseFloat(areaMinInput.value) : null;
                const areaMax = areaMaxInput.value ? parseFloat(areaMaxInput.value) : null;


                return houses.filter(el => {
                    if (q) {
                        const addr = (el.adress || '').toLowerCase();
                        if (!addr.includes(q)) return false;
                    }
                    if (hType && el.house_type_id !== hType) return false;

                    if (priceMin !== null || priceMax !== null) {
                        const priceNum = parseFloat(el.price_id);
                        if (!isNaN(priceNum)) {
                            if (priceMin !== null && priceNum < priceMin) return false;
                            if (priceMax !== null && priceNum > priceMax) return false;
                        }
                    }

                    if (areaMin !== null || areaMax !== null) {
                        const areaNum = parseFloat(el.area);
                        if (!isNaN(areaNum)) {
                            if (areaMin !== null && areaNum < areaMin) return false;
                            if (areaMax !== null && areaNum > areaMax) return false;
                        }
                    }

                    return true;
                });

            }

            function selectHouse(houseId, centerOnMap = false) {
                activeHouseId = houseId;
                

                const house = houses.find(el => Number(el.house_id) === Number(houseId));
                if (!house) return;
                
                const photos = house.photo || [];
                const photosHtml = window.PhotoCarousel
                    ? PhotoCarousel.render(photos, {
                        label: 'Фотографии:',
                        emptyText: 'Нет фотографий',
                        getSrc: (photo) => photo?.path ? `/storage/${photo.path}` : '',
                        getAlt: (photo, index) => photo?.name || `Фотография ${index + 1}`,
                    })
                    : `
                        <div class="info-label">Фотографии:</div>
                        <div>Нет фотографий</div>
                    `;
            const hasCoords = house.lat && house.lng;

            const isAuthenticated = @json(auth()->check());
            const actionsHtml = `
                <div class="house-actions">
                    ${isAuthenticated ? `
                        <a href="/house/${house.house_id}/chat" class="house-btn-order">
                            Перейти к оформлению заказа
                        </a>
                    ` : `
                        <a href="/login" class="house-btn-order">
                            Войти для оформления заказа
                        </a>
                    `}
                    ${hasCoords ? `
                        <a href="/house/${house.house_id}" class="house-btn-secondary">
                            Подробнее
                        </a>
                        <a
                            href="https://yandex.ru/maps/?rtext=~${house.lat},${house.lng}&rtt=taxi"
                            target="_blank"
                            rel="noopener"
                            class="house-btn"
                        >
                            🚕 Заказать такси
                        </a>
                    ` : ''}
                </div>
            `;


                const houseTypeName = house.house_type?.name ?? '—';
                const price = house.price_id ? (house.price_id.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽') : '—';
                
                houseInfoDiv.innerHTML = `
                <div id="house-info-card">
                    <div class="info-item">
                        <div class="info-item-label">📍 Адрес</div>
                        <div class="info-item-value-address">${house.adress ?? '—'}</div>
                    </div>
                    
                    ${photosHtml}
                    
                    <div class="info-items-row">
                        <div class="info-item">
                            <div class="info-item-label">🏠 Тип дома</div>
                            <div class="info-item-value-type">${houseTypeName}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">📐 Площадь</div>
                            <div class="info-item-value-area">${house.area ? house.area + ' м²' : '—'}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item-label">💰 Цена</div>
                        <div class="info-item-value-price">${price}</div>
                    </div>

                    ${actionsHtml}
                </div>
            `;

                // подсветка в списке
                Array.from(document.getElementsByClassName('house-item')).forEach(el => {
                    el.classList.toggle('active', Number(el.dataset.id) === Number(houseId));
                });


                if (centerOnMap && house.lat && house.lng) {
                    const lat = parseFloat(house.lat);
                    const lng = parseFloat(house.lng);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        map.setCenter([lat, lng], 17);
                    }
                }
                
                if (window.PhotoCarousel) {
                    PhotoCarousel.initAll(houseInfoDiv);
                }
            }


            resetBtn.addEventListener('click', () => {
                searchInput.value   = '';
                houseTypeSel.value  = '';
                priceMinInput.value = '';
                priceMaxInput.value = '';
                areaMinInput.value = '';
                areaMaxInput.value = '';
                updateView();
            });


            [searchInput, houseTypeSel, priceMinInput, priceMaxInput, areaMinInput, areaMaxInput].forEach(el => {
                el.addEventListener('input', updateView);
                el.addEventListener('change', updateView);
            });

            function updateView() {
                const filtered = getFilteredHouses();

                // показываем/прячем метки на Яндекс-карте
                Object.entries(markers).forEach(([id, placemark]) => {
                    const exists = filtered.find(el => Number(el.house_id) === Number(id));
                    placemark.options.set('visible', !!exists);
                });

                // рисуем список домов
                houseListDiv.innerHTML = '';

                if (filtered.length === 0) {
                    houseListDiv.innerHTML = '<div class="house-item-empty">Ничего не найдено</div>';
                } else {
                    filtered.forEach(h => {
                        const div = document.createElement('div');
                        div.className = 'house-item' + (Number(h.house_id) === Number(activeHouseId) ? ' active' : '');
                        div.dataset.id = h.house_id;
                        
                        const houseTypeName = h.house_type?.name ?? '—';
                        const price = h.price_id ? (h.price_id.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽') : '—';
                        const area = h.area ? (h.area + ' м²') : '—';
                        const address = h.adress ?? 'Адрес не указан';
                        
                        div.innerHTML = `
                            <div class="house-item-header">
                                <div class="house-item-id">#${h.house_id}</div>
                            </div>
                            <div class="house-item-address">${address}</div>
                            <div class="house-item-details">
                                <div class="house-item-detail">
                                    <span class="house-item-detail-icon">🏠</span>
                                    <span class="house-item-detail-value type">${houseTypeName}</span>
                                </div>
                                <div class="house-item-detail">
                                    <span class="house-item-detail-icon">📐</span>
                                    <span class="house-item-detail-value area">${area}</span>
                                </div>
                                <div class="house-item-detail" style="flex-basis: 100%;">
                                    <span class="house-item-detail-icon">💰</span>
                                    <span class="house-item-detail-value price">${price}</span>
                                </div>
                            </div>
                        `;
                        div.onclick = () => selectHouse(h.house_id, true);
                        houseListDiv.appendChild(div);
                    });
                }
            }

            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.dataset.target;
                    const side = btn.dataset.side;
                    const panel = document.getElementById(targetId);

                    const willCollapse = !panel.classList.contains('collapsed');
                    panel.classList.toggle('collapsed');

                    if (side === 'right') {
                        btn.textContent = willCollapse ? '❮' : '❯';
                    } else {
                        btn.textContent = willCollapse ? '❯' : '❮';
                    }
                });
            });


            updateView();


            const hash = window.location.hash;

            if (hash && hash.startsWith('#house-')) {
                const idStr = hash.replace('#house-', '');
                const houseIdFromHash = parseInt(idStr, 10);

                if (!Number.isNaN(houseIdFromHash)) {
                    selectHouse(houseIdFromHash, true);

                    const itemEl = document.querySelector(
                        `.house-item[data-id="${houseIdFromHash}"]`
                    );
                    if (itemEl) {
                        itemEl.scrollIntoView({ block: 'center', behavior: 'smooth' });
                    }
                }
            }
        }
    );
    
    </script>
@endsection
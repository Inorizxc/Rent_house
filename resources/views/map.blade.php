<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Карта домов</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            overflow: hidden;
        }

        /* Глобальная карта — всегда под всем, фуллскрин */
        #map {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
        }

        /* Слой поверх карты с панелями */
        .overlay {
            position: relative;
            z-index: 1;
            display: flex;
            height: 100vh;
            width: 100vw;
            pointer-events: none; /* клики проходят сквозь, кроме панелей */
        }

        .panel {
            pointer-events: auto;
            display: flex;
            flex-direction: column;
            background: #f7f7f9;
            border-right: 1px solid #ddd;
            padding: 12px;
            overflow-y: auto;
            width: 280px;
            min-width: 260px;
            transition: width 0.25s ease;
        }

        #left-panel {
            background: #f7f7f9;
        }

        #middle-panel {
            background: #ffffff;
        }

        #right-panel {
            background: #f7f7f9;
            border-right: none;
            border-left: 1px solid #ddd;
        }

        /* Спейсер, который отталкивает правую панель к правому краю */
        .map-spacer {
            flex: 1;
            pointer-events: none;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 36px;
            margin-bottom: 8px;
        }

        .panel-title {
            font-size: 16px;
            font-weight: 600;
            white-space: nowrap;
        }

        .toggle-btn {
            border: none;
            background: #e0e0ea;
            border-radius: 999px;
            width: 28px;
            height: 28px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            line-height: 1;
            padding: 0;
            flex-shrink: 0;
        }

        .toggle-btn:hover {
            background: #d2d2f0;
        }

        .panel-body {
            margin-top: 4px;
        }

        /* Свернутая панель: оставляем только кнопку */
        .panel.collapsed {
            width: 40px;
            min-width: 40px;
            padding: 8px 6px;
        }

        .panel.collapsed .panel-title,
        .panel.collapsed .panel-body {
            display: none;
        }

        /* Скрыть надпись OpenStreetMap */
        .leaflet-control-attribution {
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .field {
            margin-bottom: 10px;
        }

        .field label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 4px;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 6px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .filters-row {
            display: flex;
            gap: 8px;
        }

        .filters-row .field {
            flex: 1;
        }

        .btn-reset {
            margin-top: 6px;
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid #aaa;
            background: #fff;
            cursor: pointer;
        }

        .btn-reset:hover {
            background: #eee;
        }

        .house-list {
            margin-top: 8px;
        }

        .house-item {
            padding: 6px 4px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .house-item:hover {
            background: #f2f2ff;
        }

        .house-item.active {
            background: #d0d0ff;
        }

        .info-label {
            font-weight: 600;
            margin-top: 6px;
        }

        #house-info {
            font-size: 14px;
        }

        #house-info-card {
            margin-top: 4px;
            padding: 10px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        #house-info-card .info-label:first-child {
            margin-top: 0;
        }

        #house-info-empty {
            color: #666;
            font-size: 13px;
        }
    </style>
</head>

<body>
<!-- Глобальная карта -->
<div id="map"></div>

<!-- Панели поверх карты -->
<div class="overlay">
    <!-- 1 столбец: фильтры -->
    <div id="left-panel" class="panel">
        <div class="panel-header">
            <span class="panel-title">Фильтры</span>
            <!-- левая панель: открыта → стрелка влево (свернуть влево) -->
            <button class="toggle-btn" data-target="left-panel" data-side="left">❮</button>
        </div>
        <div class="panel-body">
            <div class="field">
                <label for="search">Поиск по адресу</label>
                <input type="text" id="search" placeholder="Введите адрес...">
            </div>

            <div class="field">
                <label for="rent_type">Тип аренды (rent_type_id)</label>
                <select id="rent_type">
                    <option value="">Любой</option>
                </select>
            </div>

            <div class="field">
                <label for="house_type">Тип дома (house_type_id)</label>
                <select id="house_type">
                    <option value="">Любой</option>
                </select>
            </div>

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

            <button class="btn-reset" id="resetFilters">Сбросить фильтры</button>
        </div>
    </div>

    <!-- 2 столбец: список домов -->
    <div id="middle-panel" class="panel">
        <div class="panel-header">
            <span class="panel-title">Список домов</span>
            <!-- левая часть, тоже сворачивается влево -->
            <button class="toggle-btn" data-target="middle-panel" data-side="left">❮</button>
        </div>
        <div class="panel-body">
            <div class="house-list" id="houseList">
                <!-- Список заполним из JS -->
            </div>
        </div>
    </div>

    <!-- пустое место, карта под ним, отталкивает правую панель к правому краю -->
    <div class="map-spacer"></div>

    <!-- Правая панель: детальная инфа -->
    <div id="right-panel" class="panel">
        <div class="panel-header">
            <span class="panel-title">Информация о доме</span>
            <!-- правая панель, сворачивается вправо, значит открыта → стрелка вправо -->
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

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Дома из Laravel → JS
    const houses = @json($houses);

    // Инициализация карты (глобальная)
    let defaultLat = 55.751244;
    let defaultLng = 37.618423;
    let defaultZoom = 10;

    const firstHouse = houses.find(h => h.lat && h.lng);
    if (firstHouse) {
        defaultLat = parseFloat(firstHouse.lat);
        defaultLng = parseFloat(firstHouse.lng);
        defaultZoom = 12;
    }

    const map = L.map('map').setView([defaultLat, defaultLng], defaultZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Словарь house_id -> marker
    const markers = {};
    let activeHouseId = null;

    // Создаём маркеры
    houses.forEach(h => {
        if (!h.lat || !h.lng) return;

        const lat = parseFloat(h.lat);
        const lng = parseFloat(h.lng);
        if (isNaN(lat) || isNaN( lng)) return;

        const marker = L.marker([lat, lng]).addTo(map);
        marker.on('click', () => {
            selectHouse(h.house_id, true);
        });

        markers[h.house_id] = marker;
    });

    // DOM-элементы
    const searchInput   = document.getElementById('search');
    const rentTypeSel   = document.getElementById('rent_type');
    const houseTypeSel  = document.getElementById('house_type');
    const priceMinInput = document.getElementById('price_min');
    const priceMaxInput = document.getElementById('price_max');
    const houseListDiv  = document.getElementById('houseList');
    const houseInfoDiv  = document.getElementById('house-info');
    const resetBtn      = document.getElementById('resetFilters');

    // Заполнение select’ов уникальными значениями
    function fillSelectOptions() {
        const rentTypes  = new Set();
        const houseTypes = new Set();

        houses.forEach(h => {
            if (h.rent_type_id)  rentTypes.add(h.rent_type_id);
            if (h.house_type_id) houseTypes.add(h.house_type_id);
        });

        rentTypes.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
            rentTypeSel.appendChild(opt);
        });

        houseTypes.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
            houseTypeSel.appendChild(opt);
        });
    }

    fillSelectOptions();

    // Получить отфильтрованный список домов
    function getFilteredHouses() {
        const q        = searchInput.value.trim().toLowerCase();
        const rType    = rentTypeSel.value;
        const hType    = houseTypeSel.value;
        const priceMin = priceMinInput.value ? parseFloat(priceMinInput.value) : null;
        const priceMax = priceMaxInput.value ? parseFloat(priceMaxInput.value) : null;

        return houses.filter(h => {
            if (q) {
                const addr = (h.adress || '').toLowerCase();
                if (!addr.includes(q)) return false;
            }

            if (rType && h.rent_type_id !== rType) return false;
            if (hType && h.house_type_id !== hType) return false;

            if (priceMin !== null || priceMax !== null) {
                const priceNum = parseFloat(h.price_id);
                if (!isNaN(priceNum)) {
                    if (priceMin !== null && priceNum < priceMin) return false;
                    if (priceMax !== null && priceNum > priceMax) return false;
                }
            }

            return true;
        });
    }

    // Обновление списка домов и видимости маркеров
    function updateView() {
        const filtered = getFilteredHouses();

        // Маркеры
        Object.entries(markers).forEach(([id, marker]) => {
            const exists = filtered.find(h => Number(h.house_id) === Number(id));
            if (exists) {
                if (!map.hasLayer(marker)) marker.addTo(map);
            } else {
                if (map.hasLayer(marker)) map.removeLayer(marker);
            }
        });

        // Список
        houseListDiv.innerHTML = '';

        if (filtered.length === 0) {
            houseListDiv.innerHTML = '<div style="font-size:13px;color:#666;">Ничего не найдено</div>';
        } else {
            filtered.forEach(h => {
                const div = document.createElement('div');
                div.className = 'house-item' + (Number(h.house_id) === Number(activeHouseId) ? ' active' : '');
                div.dataset.id = h.house_id;
                div.innerHTML = `
                    <div><strong>#${h.house_id}</strong> — ${h.adress ?? 'Адрес не указан'}</div>
                    <div style="font-size:12px;color:#555;">
                        Тип аренды: ${h.rent_type_id ?? '—'} |
                        Тип дома: ${h.house_type_id ?? '—'} |
                        Цена: ${h.price_id ?? '—'}
                    </div>
                `;
                div.onclick = () => selectHouse(h.house_id, true);
                houseListDiv.appendChild(div);
            });
        }
    }

    // Выбор дома
    function selectHouse(houseId, centerOnMap = false) {
        activeHouseId = houseId;

        const house = houses.find(h => Number(h.house_id) === Number(houseId));
        if (!house) return;

        houseInfoDiv.innerHTML = `
            <div id="house-info-card">
                <div class="info-label">ID дома:</div> ${house.house_id}
                <div class="info-label">Адрес:</div> ${house.adress ?? '—'}
                <div class="info-label">Площадь:</div> ${house.area ?? '—'}
                <div class="info-label">Тип аренды:</div> ${house.rent_type_id ?? '—'}
                <div class="info-label">Тип дома:</div> ${house.house_type_id ?? '—'}
                <div class="info-label">Цена:</div> ${house.price_id ?? '—'}
                <div class="info-label">Координаты:</div> ${house.lat}, ${house.lng}
            </div>
        `;

        Array.from(document.getElementsByClassName('house-item')).forEach(el => {
            el.classList.toggle('active', Number(el.dataset.id) === Number(houseId));
        });

        if (centerOnMap && house.lat && house.lng) {
            const lat = parseFloat(house.lat);
            const lng = parseFloat(house.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 20);
            }
        }
    }

    // Сброс фильтров
    resetBtn.addEventListener('click', () => {
        searchInput.value   = '';
        rentTypeSel.value   = '';
        houseTypeSel.value  = '';
        priceMinInput.value = '';
        priceMaxInput.value = '';
        updateView();
    });

    [searchInput, rentTypeSel, houseTypeSel, priceMinInput, priceMaxInput].forEach(el => {
        el.addEventListener('input', updateView);
        el.addEventListener('change', updateView);
    });

    // Тоггл панелей — карта не двигается, просто под ними
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

            // Центр карты остаётся тем же, просто на всякий случай говорим Leaflet пересчитать контейнер
            const center = map.getCenter();
            const zoom = map.getZoom();
            setTimeout(() => {
                map.invalidateSize();
                map.setView(center, zoom, { animate: false });
            }, 260);
        });
    });

    // Первый рендер
    updateView();
</script>

</body>
</html>

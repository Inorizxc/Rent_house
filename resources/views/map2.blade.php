@extends('layout')

@section('title')
    Карта домов
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
    
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script >
        
    const houses = @json($houses);
    console.log(houses);

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

    const map = L.map('map', {
        zoomControl: false,
        attributionControl: false
    }).setView([defaultLat, defaultLng], defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    const markers = {}
    let activeHouseId = null;

    houses.forEach(el => {
        if (!el.lat || !el.lng) return;

        const lat = parseFloat(el.lat);
        const lng = parseFloat(el.lng);

        if (isNaN(lat) || isNaN(lng)) return;
        const marker = L.marker([lat, lng]).addTo(map);

        marker.on('click', () => {
            selectHouse(el.house_id, true);
        });

        markers[el.house_id] = marker;
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
        const houseTypes = new Set();

        houses.forEach(el => {
            if (el.house_type_id) houseTypes.add(el.house_type_id);
        });

        houseTypes.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
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

    function updateView() {
        const filtered = getFilteredHouses();

        Object.entries(markers).forEach(([id, marker]) => {
            const exists = filtered.find(el => Number(el.house_id) === Number(id));
            if (exists) {
                if (!map.hasLayer(marker)) marker.addTo(map);
            } else {
                if (map.hasLayer(marker)) map.removeLayer(marker);
            }
        });

        houseListDiv.innerHTML = '';

        if (filtered.length === 0) {
            houseListDiv.innerHTML = '<div>Ничего не найдено</div>';
        } else {
            filtered.forEach(h => {
                const div = document.createElement('div');
                div.className = 'house-item' + (Number(h.house_id) === Number(activeHouseId) ? ' active' : '');
                div.dataset.id = h.house_id;
                div.innerHTML = `
                    <div><strong>#${h.house_id}</strong> — ${h.adress ?? 'Адрес не указан'}</div>
                    <div>
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


    houseInfoDiv.innerHTML = `
        <div id="house-info-card">
            <div class="info-label">ID дома:</div> ${house.house_id}
            <div class="info-label">Адрес:</div> ${house.adress ?? '—'}
            <div class="info-label">Площадь:</div> ${house.area ?? '—'}
            <div class="info-label">Тип дома:</div> ${house.house_type_id ?? '—'}
            <div class="info-label">Цена:</div> ${house.price_id ?? '—'}
            <div class="info-label">Координаты:</div> ${house.lat}, ${house.lng}

            <br>
            ${photosHtml}
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
            map.setView([lat, lng], 17);
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


    function saveMapView() {
        const center = map.getCenter();
        const zoom = map.getZoom();

        localStorage.setItem(
            'housesMapView',
            JSON.stringify({
                lat: center.lat,
                lng: center.lng,
                zoom: zoom,
            })
        );
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

    map.on('moveend', saveMapView);
    map.on('zoomend', saveMapView);
    updateView();
    </script>
@endsection
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
                        <label for="price_min">Площадь от</label>
                        <input type="number" id="price_min" placeholder="мин">
                    </div>
                    <div class="field">
                        <label for="price_max">Площадь до</label>
                        <input type="number" id="price_max" placeholder="макс">
                    </div>
                </div>


                <button class="btn-reset" id="resetFilters">Сбросить фильтры</button>
            </div>
        </div>
        
        <div class="panel" id="middle-panel">
            <div class="panel-header">
                <span class="panel-title">Список домов</span>
            </div>
            <div class="panel-body">
                <div class="house-list" id="houseList"></div>
            </div>
        </div>

        <div class="map-spacer"></div>


        <div class="panel" id="right-panel">
            <div class="panel-header">
                <span class="panel-title">Информация о доме</span>
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
        
    const houses = @json($houses);


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

    const map = L.map('map').setView([defaultLat, defaultLng], defaultZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    const markers = {}
    let activeHouseId = null;

    houses.forEach(el => {
        if (!el.lat || !el.lng) return;

        const lat = parseFloat(el.lat);
        const lng = parseFloat(el.lng);

        if (isNaN(lat) || isNaN(lng)) return;
        const marker = L.marker([lat, lng]).addTo(map);

        // marker.on('click', () => {
        // });

        markers[el.house_id] = marker;
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

    // Сохраняем, когда пользователь завершил перетаскивание или изменение зума
    map.on('moveend', saveMapView);
    map.on('zoomend', saveMapView);
    </script>
@endsection
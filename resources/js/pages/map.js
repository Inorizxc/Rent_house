/**
 * Модуль для работы с картой домов
 */

import { formatPrice } from '../modules/utils.js';

class MapManager {
    constructor(houses) {
        this.houses = houses;
        this.markers = {};
        this.activeHouseId = null;
        this.map = null;
        
        this.init();
    }

    init() {
        if (typeof ymaps === 'undefined') {
            console.error('Yandex Maps API не загружена');
            return;
        }

        ymaps.ready(() => {
            this.initMap();
            this.initFilters();
            this.initPanels();
            this.loadFromHash();
        });
    }

    initMap() {
        let defaultLat = 51.533338;
        let defaultLng = 46.034176;
        let defaultZoom = 10;

        // Загружаем сохраненное состояние карты
        const savedViewRaw = localStorage.getItem('housesMapView');
        if (savedViewRaw) {
            try {
                const savedView = JSON.parse(savedViewRaw);
                if (
                    typeof savedView.lat === 'number' &&
                    typeof savedView.lng === 'number' &&
                    typeof savedView.zoom === 'number'
                ) {
                    defaultLat = savedView.lat;
                    defaultLng = savedView.lng;
                    defaultZoom = savedView.zoom;
                }
            } catch (e) {
                console.warn('Ошибка сохранения состояния карты:', e);
            }
        }

        this.map = new ymaps.Map('map', {
            center: [defaultLat, defaultLng],
            zoom: defaultZoom,
            controls: []
        });

        // Сохраняем состояние карты при изменении
        this.map.events.add('boundschange', () => this.saveMapView());

        // Создаем метки для домов
        this.createMarkers();
    }

    createMarkers() {
        this.houses.forEach(house => {
            if (!house.lat || !house.lng) return;

            const lat = parseFloat(house.lat);
            const lng = parseFloat(house.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const placemark = new ymaps.Placemark(
                [lat, lng],
                {
                    balloonContentHeader: `Дом #${house.house_id}`,
                    balloonContentBody: house.adress ?? 'Адрес не указан',
                },
                {
                    preset: 'islands#blueIcon'
                }
            );

            placemark.events.add('click', () => {
                this.selectHouse(house.house_id, true);
            });

            this.markers[house.house_id] = placemark;
            this.map.geoObjects.add(placemark);
        });
    }

    saveMapView() {
        const center = this.map.getCenter();
        const zoom = this.map.getZoom();

        localStorage.setItem(
            'housesMapView',
            JSON.stringify({
                lat: center[0],
                lng: center[1],
                zoom: zoom,
            })
        );
    }

    initFilters() {
        const searchInput = document.getElementById('search');
        const houseTypeSel = document.getElementById('house_type');
        const priceMinInput = document.getElementById('price_min');
        const priceMaxInput = document.getElementById('price_max');
        const areaMinInput = document.getElementById('area_min');
        const areaMaxInput = document.getElementById('area_max');
        const resetBtn = document.getElementById('resetFilters');

        // Заполняем селект типов домов
        this.fillSelectOptions(houseTypeSel);

        // Обработчики фильтров
        [searchInput, houseTypeSel, priceMinInput, priceMaxInput, areaMinInput, areaMaxInput].forEach(el => {
            el.addEventListener('input', () => this.updateView());
            el.addEventListener('change', () => this.updateView());
        });

        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            houseTypeSel.value = '';
            priceMinInput.value = '';
            priceMaxInput.value = '';
            areaMinInput.value = '';
            areaMaxInput.value = '';
            this.updateView();
        });

        // Первоначальное обновление
        this.updateView();
    }

    fillSelectOptions(select) {
        const houseTypes = new Map();

        this.houses.forEach(house => {
            if (house.house_type && house.house_type.name) {
                houseTypes.set(house.house_type.house_type_id, house.house_type.name);
            }
        });

        houseTypes.forEach((name, id) => {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = name;
            select.appendChild(opt);
        });
    }

    getFilteredHouses() {
        const searchInput = document.getElementById('search');
        const houseTypeSel = document.getElementById('house_type');
        const priceMinInput = document.getElementById('price_min');
        const priceMaxInput = document.getElementById('price_max');
        const areaMinInput = document.getElementById('area_min');
        const areaMaxInput = document.getElementById('area_max');

        const q = searchInput.value.trim().toLowerCase();
        const hType = houseTypeSel.value;
        const priceMin = priceMinInput.value ? parseFloat(priceMinInput.value) : null;
        const priceMax = priceMaxInput.value ? parseFloat(priceMaxInput.value) : null;
        const areaMin = areaMinInput.value ? parseFloat(areaMinInput.value) : null;
        const areaMax = areaMaxInput.value ? parseFloat(areaMaxInput.value) : null;

        return this.houses.filter(house => {
            if (q) {
                const addr = (house.adress || '').toLowerCase();
                if (!addr.includes(q)) return false;
            }
            if (hType && house.house_type_id !== hType) return false;

            if (priceMin !== null || priceMax !== null) {
                const priceNum = parseFloat(house.price_id);
                if (!isNaN(priceNum)) {
                    if (priceMin !== null && priceNum < priceMin) return false;
                    if (priceMax !== null && priceNum > priceMax) return false;
                }
            }

            if (areaMin !== null || areaMax !== null) {
                const areaNum = parseFloat(house.area);
                if (!isNaN(areaNum)) {
                    if (areaMin !== null && areaNum < areaMin) return false;
                    if (areaMax !== null && areaNum > areaMax) return false;
                }
            }

            return true;
        });
    }

    selectHouse(houseId, centerOnMap = false) {
        this.activeHouseId = houseId;

        const house = this.houses.find(h => Number(h.house_id) === Number(houseId));
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
        const isAuthenticated = window.isAuthenticated || false;
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
        const price = formatPrice(house.price_id);

        const houseInfoDiv = document.getElementById('house-info');
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

        // Подсветка в списке
        Array.from(document.getElementsByClassName('house-item')).forEach(el => {
            el.classList.toggle('active', Number(el.dataset.id) === Number(houseId));
        });

        if (centerOnMap && house.lat && house.lng) {
            const lat = parseFloat(house.lat);
            const lng = parseFloat(house.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                this.map.setCenter([lat, lng], 17);
            }
        }

        if (window.PhotoCarousel) {
            PhotoCarousel.initAll(houseInfoDiv);
        }
    }

    updateView() {
        const filtered = this.getFilteredHouses();

        // Показываем/прячем метки на карте
        Object.entries(this.markers).forEach(([id, placemark]) => {
            const exists = filtered.find(h => Number(h.house_id) === Number(id));
            placemark.options.set('visible', !!exists);
        });

        // Рисуем список домов
        const houseListDiv = document.getElementById('houseList');
        houseListDiv.innerHTML = '';

        if (filtered.length === 0) {
            houseListDiv.innerHTML = '<div class="house-item-empty">Ничего не найдено</div>';
        } else {
            filtered.forEach(house => {
                const div = document.createElement('div');
                div.className = 'house-item' + (Number(house.house_id) === Number(this.activeHouseId) ? ' active' : '');
                div.dataset.id = house.house_id;

                const houseTypeName = house.house_type?.name ?? '—';
                const price = formatPrice(house.price_id);
                const area = house.area ? (house.area + ' м²') : '—';
                const address = house.adress ?? 'Адрес не указан';

                div.innerHTML = `
                    <div class="house-item-header">
                        <div class="house-item-id">#${house.house_id}</div>
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
                div.onclick = () => this.selectHouse(house.house_id, true);
                houseListDiv.appendChild(div);
            });
        }
    }

    initPanels() {
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
    }

    loadFromHash() {
        const hash = window.location.hash;
        if (hash && hash.startsWith('#house-')) {
            const idStr = hash.replace('#house-', '');
            const houseIdFromHash = parseInt(idStr, 10);

            if (!Number.isNaN(houseIdFromHash)) {
                this.selectHouse(houseIdFromHash, true);

                const itemEl = document.querySelector(
                    `.house-item[data-id="${houseIdFromHash}"]`
                );
                if (itemEl) {
                    itemEl.scrollIntoView({ block: 'center', behavior: 'smooth' });
                }
            }
        }
    }
}

// Инициализация при загрузке страницы
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMap);
} else {
    initMap();
}

function initMap() {
    if (window.housesData) {
        new MapManager(window.housesData);
    }
}


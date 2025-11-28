/**
 * Модуль для работы с вкладками профиля
 */

// Функция для инициализации фото-каруселей
function initPhotoCarousels(container) {
    const photoBlocks = container.querySelectorAll('[data-house-photos]');
    photoBlocks.forEach(block => {
        const raw = block.dataset.housePhotos || '[]';
        let photos = [];
        try {
            photos = JSON.parse(raw);
        } catch (e) {
            console.warn('Не удалось распарсить фото для карусели', e);
        }

        if (window.PhotoCarousel) {
            PhotoCarousel.mount(block, photos, {
                hideLabel: true,
                emptyText: block.dataset.emptyText || 'Нет фотографий',
                getSrc: (photo) => photo?.path ? `/storage/${photo.path}` : '',
                getAlt: (photo, index) => photo?.name || `Фотография ${index + 1}`,
            });
        }
    });
}

// Функция для определения активной вкладки из URL
function getActiveTabFromURL() {
    const path = window.location.pathname;
    if (path.match(/\/tab\/settings/)) return 'settings';
    if (path.match(/\/tab\/orders/)) return 'orders';
    if (path.match(/\/tab\/houses/)) return 'houses';
    return 'houses';
}

// Функция для загрузки контента вкладки через AJAX
async function loadTab(tab, route, panel) {
    if (!panel) return;

    panel.innerHTML = '<div class="profile-empty">Загрузка...</div>';

    try {
        const response = await fetch(route, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        });

        if (!response.ok) {
            throw new Error('Ошибка загрузки вкладки');
        }

        const html = await response.text();
        panel.innerHTML = html;

        // Выполняем скрипты из загруженного HTML
        const scripts = panel.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            if (oldScript.src) {
                newScript.src = oldScript.src;
            } else {
                newScript.textContent = oldScript.textContent;
            }
            document.body.appendChild(newScript);
            oldScript.remove();
        });

        // Небольшая задержка для обновления DOM
        setTimeout(() => {
            initPhotoCarousels(panel);

            if (window.initHouseCalendars) {
                window.initHouseCalendars();
            }

            if (window.initOrdersFilters && tab === 'orders') {
                let attempts = 0;
                const tryInit = () => {
                    attempts++;
                    const grid = panel.querySelector('#orders-houses-grid');
                    const buttons = panel.querySelectorAll('.orders-filter-btn');
                    if (grid && buttons.length > 0) {
                        window.initOrdersFilters(panel);
                    } else if (attempts < 5) {
                        setTimeout(tryInit, 100);
                    }
                };
                setTimeout(tryInit, 100);
            }

            if (window.initHousesFilters && tab === 'houses') {
                let attempts = 0;
                const tryInit = () => {
                    attempts++;
                    const grid = panel.querySelector('#orders-houses-grid');
                    const checkboxes = panel.querySelectorAll('input[type="checkbox"][data-filter-rent-type], input[type="checkbox"][data-filter-house-type]');
                    if (grid && checkboxes.length > 0) {
                        window.initHousesFilters(panel);
                    } else if (attempts < 5) {
                        setTimeout(tryInit, 100);
                    }
                };
                setTimeout(tryInit, 100);
            }
        }, 100);
    } catch (error) {
        console.error('Ошибка загрузки вкладки:', error);
        panel.innerHTML = '<div class="profile-empty">Ошибка загрузки. Попробуйте обновить страницу.</div>';
    }
}

// Функция для проверки наличия контента в панели
function hasContent(panel) {
    if (!panel) return false;
    return (
        panel.querySelector('.houses-grid') !== null ||
        panel.querySelector('.orders-houses-grid') !== null ||
        panel.querySelector('.orders-house-card') !== null ||
        panel.querySelector('.orders-compact-card') !== null ||
        panel.querySelector('.settings-tab-content') !== null ||
        (panel.querySelector('.profile-empty') !== null &&
         !panel.innerHTML.includes('Загрузка...') &&
         panel.textContent.trim() !== 'Загрузка...' &&
         panel.textContent.trim() !== '')
    );
}

// Функция для переключения вкладки
function switchTab(tab, route, buttons, panels, skipLoad = false) {
    const btn = Array.from(buttons).find(b => b.dataset.tab === tab);
    if (!btn) return;

    const panel = document.getElementById('tab-' + tab);
    const hasContentInPanel = hasContent(panel);

    // Обновляем активные кнопки и панели
    buttons.forEach(b => b.classList.remove('active'));
    panels.forEach(p => p.classList.remove('active'));
    btn.classList.add('active');

    if (panel) {
        panel.classList.add('active');
    }

    // Обновляем URL
    if (route) {
        window.history.replaceState({ tab, route }, '', route);
    }

    // Загружаем контент через AJAX только если его нет и не пропущена загрузка
    if (route && !skipLoad && !hasContentInPanel) {
        loadTab(tab, route, panel);
    } else if (hasContentInPanel && panel) {
        initPhotoCarousels(panel);
        if (window.initHouseCalendars) {
            window.initHouseCalendars();
        }
        if (window.initOrdersFilters && tab === 'orders') {
            window.initOrdersFilters(panel);
        }
    }
}

// Инициализация при загрузке страницы
function initProfileTabs() {
    const buttons = document.querySelectorAll('.profile-tab-btn');
    const panels = document.querySelectorAll('.profile-tab-panel');
    const tabPanels = document.querySelector('.profile-tab-panels');
    const userId = tabPanels?.dataset.userId;

    if (!buttons.length || !panels.length) {
        return;
    }

    // Проверяем наличие основных элементов профиля
    const profileWrapper = document.querySelector('.profile-wrapper');
    const profileHeader = document.querySelector('.profile-header');

    if (!profileWrapper || !profileHeader) {
        window.location.reload();
        return;
    }

    // Обработчик клика по вкладкам
    buttons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const tab = btn.dataset.tab;
            const route = btn.dataset.route;
            switchTab(tab, route, buttons, panels);
        });
    });

    // Обработчик события popstate
    window.addEventListener('popstate', (e) => {
        const currentPath = window.location.pathname;

        const profileWrapper = document.querySelector('.profile-wrapper');
        const profileHeader = document.querySelector('.profile-header');

        if (!profileWrapper || !profileHeader) {
            window.location.href = currentPath;
            return;
        }

        if (currentPath && currentPath.includes('/tab/')) {
            window.location.href = currentPath;
            return;
        }

        window.location.reload();
    });

    // Определяем активную вкладку при первой загрузке
    const activeTab = getActiveTabFromURL();
    const activePanel = document.getElementById('tab-' + activeTab);

    if (activePanel) {
        const btn = Array.from(buttons).find(b => b.dataset.tab === activeTab);
        if (btn) {
            buttons.forEach(b => b.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            activePanel.classList.add('active');
        }

        const hasContentInPanel = hasContent(activePanel);

        if (hasContentInPanel) {
            initPhotoCarousels(activePanel);
            if (window.initHouseCalendars) {
                window.initHouseCalendars();
            }
            if (window.initOrdersFilters && activeTab === 'orders') {
                window.initOrdersFilters(activePanel);
            }
        } else {
            if (btn && btn.dataset.route) {
                loadTab(activeTab, btn.dataset.route, activePanel);
            }
        }
    }
}

// Инициализируем при загрузке DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProfileTabs);
} else {
    initProfileTabs();
}

// Также инициализируем при полной загрузке страницы
window.addEventListener('load', () => {
    setTimeout(initProfileTabs, 100);
});


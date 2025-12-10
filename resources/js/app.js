/**
 * Главная точка входа для всего приложения
 * Использует динамические импорты для code splitting
 */

// Предзагружаем общие модули сразу
import * as utils from './modules/utils.js';
import { showBanNotification } from './modules/notifications.js';
import { PollingManager } from './modules/polling.js';
import './modules/photo-carousel.js';

// Экспортируем в window для глобального доступа
window.utils = utils;
window.showBanNotification = showBanNotification;
window.PollingManager = PollingManager;

/**
 * Определяет текущую страницу на основе DOM элементов
 */
function detectPage() {
    const path = window.location.pathname;
    
    // Проверяем наличие специфичных элементов на странице
    if (document.getElementById('chatMessages')) {
        return 'chat';
    }
    
    if (document.getElementById('map') && window.housesData) {
        return 'map';
    }
    
    if (document.querySelector('.profile-tab-btn')) {
        return 'profile';
    }
    
    if (document.getElementById('orders-houses-grid')) {
        return 'orders-filters';
    }
    
    return 'default';
}

/**
 * Условная загрузка модулей страницы
 */
async function initPageModules() {
    const page = detectPage();
    
    switch (page) {
        case 'chat':
            if (window.chatConfig) {
                await import('./pages/chat.js');
            }
            break;
            
        case 'map':
            if (window.housesData) {
                // Ждем загрузки и готовности Yandex Maps
                const initMapModule = async () => {
                    await import('./pages/map.js');
                };
                
                if (typeof ymaps !== 'undefined') {
                    if (typeof ymaps.ready === 'function') {
                        ymaps.ready(initMapModule);
                    } else {
                        initMapModule();
                    }
                } else {
                    // Если ymaps еще не загружен, ждем его
                    const checkYmaps = setInterval(() => {
                        if (typeof ymaps !== 'undefined') {
                            clearInterval(checkYmaps);
                            if (typeof ymaps.ready === 'function') {
                                ymaps.ready(initMapModule);
                            } else {
                                initMapModule();
                            }
                        }
                    }, 100);
                    
                    // Таймаут на случай если ymaps не загрузится
                    setTimeout(() => {
                        clearInterval(checkYmaps);
                    }, 10000);
                }
            }
            break;
            
        case 'profile':
            await import('./pages/profile-tabs.js');
            break;
            
        case 'orders-filters':
            await import('./pages/orders-filters.js');
            break;
    }
}

/**
 * Инициализация меню пользователя (загружается на всех страницах для авторизованных)
 */
function initUserMenu() {
    if (document.getElementById('userMenuToggle')) {
        // Загружаем только если есть элемент меню
        import('./pages/user-menu.js');
    }
}

// Инициализация при загрузке DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initUserMenu();
        initPageModules();
    });
} else {
    initUserMenu();
    initPageModules();
}

// Также инициализируем при полной загрузке страницы (для динамически загруженного контента)
window.addEventListener('load', () => {
    setTimeout(() => {
        initPageModules();
    }, 100);
});


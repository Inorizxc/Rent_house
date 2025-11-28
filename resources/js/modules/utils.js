/**
 * Общие утилиты для работы с DOM и API
 */

// Получение CSRF токена
export function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// Обертка для fetch с автоматическим добавлением CSRF токена
export async function apiFetch(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            ...(options.headers || {})
        },
        ...options
    };

    const response = await fetch(url, defaultOptions);
    
    if (!response.ok) {
        const error = await response.json().catch(() => ({ 
            error: `Ошибка ${response.status}: ${response.statusText}` 
        }));
        throw new Error(error.error || error.message || 'Ошибка запроса');
    }
    
    return response.json();
}

// Форматирование даты
export function formatDate(dateString, options = {}) {
    const date = new Date(dateString);
    const defaultOptions = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        ...options
    };
    return date.toLocaleDateString('ru-RU', defaultOptions);
}

// Прокрутка элемента вниз
export function scrollToBottom(element) {
    if (element) {
        element.scrollTop = element.scrollHeight;
    }
}

// Проверка, прокручен ли элемент до конца
export function isScrolledToBottom(element, threshold = 100) {
    if (!element) return false;
    return element.scrollHeight - element.scrollTop <= element.clientHeight + threshold;
}

// Дебаунс функция
export function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle функция
export function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Экранирование HTML
export function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Форматирование цены
export function formatPrice(price) {
    if (!price) return '—';
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
}


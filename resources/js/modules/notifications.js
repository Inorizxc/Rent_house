/**
 * Модуль для показа уведомлений
 */

const STYLE_ID = 'notification-styles';

function ensureStyles() {
    if (document.getElementById(STYLE_ID)) {
        return;
    }

    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Показывает уведомление о бане
 * @param {string} message - Текст сообщения
 * @param {number} duration - Длительность показа в миллисекундах (по умолчанию 8000)
 */
export function showBanNotification(message, duration = 8000) {
    ensureStyles();

    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #ffffff;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(220, 38, 38, 0.4);
        z-index: 10000;
        max-width: 400px;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.5;
        animation: slideInRight 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <svg style="width: 24px; height: 24px; flex-shrink: 0; margin-top: 2px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
            </svg>
            <div style="flex: 1;">
                <div style="font-weight: 600; margin-bottom: 4px; font-size: 15px;">Ваш аккаунт заблокирован</div>
                <div style="opacity: 0.95; font-size: 13px;">${escapeHtml(message)}</div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: #ffffff; cursor: pointer; font-size: 20px; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">×</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматически удаляем через указанное время
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, duration);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}


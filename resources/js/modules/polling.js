/**
 * Модуль для периодического опроса сервера (polling)
 */

export class PollingManager {
    constructor(callback, interval = 2000) {
        this.callback = callback;
        this.interval = interval;
        this.isPolling = false;
        this.pollingInterval = null;
    }

    start() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollingInterval = setInterval(() => {
            if (this.isPolling) {
                this.callback();
            }
        }, this.interval);
    }

    stop() {
        this.isPolling = false;
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    restart() {
        this.stop();
        this.start();
    }

    // Обработка изменения видимости страницы
    handleVisibilityChange() {
        if (document.hidden) {
            this.stop();
        } else {
            this.start();
            // Сразу вызываем callback при возврате
            this.callback();
        }
    }

    // Инициализация с автоматической обработкой видимости страницы
    init() {
        this.start();
        
        // Останавливаем polling при уходе со страницы
        window.addEventListener('beforeunload', () => {
            this.stop();
        });

        // Обрабатываем изменение видимости вкладки
        document.addEventListener('visibilitychange', () => {
            this.handleVisibilityChange();
        });
    }
}


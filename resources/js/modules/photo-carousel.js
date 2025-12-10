/**
 * Модуль фото-карусели
 * Перенесен из public/js/photo-carousel.js в Vite build
 */

(function (global) {
    if (!global || global.PhotoCarousel) {
        return;
    }

    const STYLE_ID = 'photo-carousel-styles';
    const DEFAULTS = {
        label: 'Фотографии:',
        emptyText: 'Нет фотографий',
        prevLabel: '❮',
        nextLabel: '❯',
        getSrc: (photo) => {
            if (!photo) return '';
            if (typeof photo === 'string') return photo;
            if (photo.url) return photo.url;
            if (photo.path) return `/storage/${photo.path}`;
            return '';
        },
        getAlt: (photo, index) => {
            if (!photo) return `Фотография ${index + 1}`;
            return photo.name || photo.alt || `Фотография ${index + 1}`;
        },
    };

    function escapeHtml(value = '') {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function ensureStyles() {
        if (document.getElementById(STYLE_ID)) {
            return;
        }

        const style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = `
.photo-carousel {
    margin-top: 10px;
    position: relative;
}

.photos-viewport {
    position: relative;
    overflow: hidden;
    width: 100%;
    height: 230px;
    border-radius: 10px;
}

.photos-strip {
    display: flex;
    height: 100%;
    transition: transform 0.35s ease;
}

.house-photo {
    flex: 0 0 100%;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 999px;
    border: 1px solid rgba(0, 0, 0, 0.12);
    background: rgba(255, 255, 255, 0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 17px;
    line-height: 1;
    padding: 0;
    transition: background 0.2s, transform 0.15s, border-color 0.2s;
    z-index: 2;
    user-select: none;
}

.photo-nav.prev {
    left: 10px;
}

.photo-nav.next {
    right: 10px;
}

.photo-nav:hover {
    background: rgba(255, 255, 255, 0.9);
    border-color: rgba(0, 0, 0, 0.2);
    transform: translateY(-50%) scale(1.05);
}

.photo-nav:active {
    transform: translateY(-50%) scale(0.96);
}

.photo-carousel__empty {
    margin-top: 8px;
    padding: 18px 16px;
    border-radius: 10px;
    background: #f3f4f6;
    color: #6b7280;
    text-align: center;
    font-size: 13px;
}`;

        document.head.appendChild(style);
    }

    function render(photos = [], options = {}) {
        ensureStyles();
        const opts = { ...DEFAULTS, ...options };
        const hasLabel = !opts.hideLabel && opts.label;

        if (!Array.isArray(photos) || photos.length === 0) {
            return `
                ${hasLabel ? `<div class="info-label">${escapeHtml(opts.label)}</div>` : ''}
                <div class="photo-carousel__empty">${escapeHtml(opts.emptyText)}</div>
            `;
        }

        const slides = photos
            .map((photo, index) => {
                const src = opts.getSrc(photo, index);
                if (!src) {
                    return '';
                }
                const alt = opts.getAlt(photo, index);
                return `<img src="${escapeHtml(src)}" class="house-photo" alt="${escapeHtml(alt)}" loading="lazy">`;
            })
            .join('');

        return `
            ${hasLabel ? `<div class="info-label">${escapeHtml(opts.label)}</div>` : ''}
            <div class="photo-carousel" data-photo-carousel>
                <button class="photo-nav prev" type="button" aria-label="Предыдущая фотография">${escapeHtml(opts.prevLabel)}</button>
                <div class="photos-viewport">
                    <div class="photos-strip">
                        ${slides}
                    </div>
                </div>
                <button class="photo-nav next" type="button" aria-label="Следующая фотография">${escapeHtml(opts.nextLabel)}</button>
            </div>
        `;
    }

    function init(carousel) {
        if (!carousel || carousel.dataset.photoCarouselReady === '1') {
            return;
        }

        const viewport = carousel.querySelector('.photos-viewport');
        const strip = carousel.querySelector('.photos-strip');
        const slides = Array.from(strip ? strip.querySelectorAll('.house-photo') : []);

        if (!viewport || !strip || slides.length === 0) {
            return;
        }

        let currentIndex = 0;
        const total = slides.length;
        const prevBtn = carousel.querySelector('.photo-nav.prev');
        const nextBtn = carousel.querySelector('.photo-nav.next');

        function show(index) {
            if (index < 0) index = total - 1;
            if (index >= total) index = 0;
            currentIndex = index;

            const slideWidth = viewport.clientWidth;
            strip.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
        }

        const handlePrev = () => show(currentIndex - 1);
        const handleNext = () => show(currentIndex + 1);
        const handleResize = () => show(currentIndex);

        prevBtn?.addEventListener('click', handlePrev);
        nextBtn?.addEventListener('click', handleNext);
        window.addEventListener('resize', handleResize);

        carousel.dataset.photoCarouselReady = '1';
        carousel.__photoCarouselCleanup = () => {
            prevBtn?.removeEventListener('click', handlePrev);
            nextBtn?.removeEventListener('click', handleNext);
            window.removeEventListener('resize', handleResize);
            carousel.dataset.photoCarouselReady = '0';
        };

        show(0);
    }

    function initAll(root = document) {
        ensureStyles();
        const carousels = root.querySelectorAll
            ? root.querySelectorAll('[data-photo-carousel]')
            : [];
        carousels.forEach(init);
    }

    function mount(container, photos, options) {
        if (!container) return null;
        container.innerHTML = render(photos, options);
        initAll(container);
        return container.querySelector('[data-photo-carousel]');
    }

    global.PhotoCarousel = {
        render,
        init,
        initAll,
        mount,
    };
})(typeof window !== 'undefined' ? window : undefined);


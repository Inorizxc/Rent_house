@extends('layout')

@section('title')
    Профиль
@endsection

@section('style')
    body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f6f6f7;
        overflow: hidden;
        height: 100vh;
    }

    .profile-wrapper {
        padding: 20px 12px 12px; /* отступ от шапки */
        max-width: 1400px;
        margin: 0 auto;
        height: calc(100vh - 45px - 42px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Шапка с аватаром и ФИО */
    .profile-header {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        margin-bottom: 18px;
        flex-shrink: 0;
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
    }

    .profile-header-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .profile-name {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }

    .profile-rating {
        font-size: 14px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .profile-rating-stars {
        color: #f59e0b;
        font-size: 16px;
        line-height: 1;
    }

    /* Основной макет: левый столбец + контент справа */
    .profile-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 18px;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    /* Левая панель */
    .profile-sidebar {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
        padding: 16px 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        overflow: hidden;
    }

    .profile-sidebar-top {
        font-size: 14px;
        color: #4b5563;
    }

    .profile-sidebar-bottom {
        margin-top: 16px;
    }

    .profile-sidebar-button {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, transform 0.1s;
    }

    .profile-sidebar-button:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        transform: translateY(-1px);
    }

    /* Правая часть: вкладки и контент */
    .profile-main {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        padding: 12px 14px 14px;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    .profile-tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 10px;
        gap: 4px;
        flex-shrink: 0;
    }

    .profile-tab-btn {
        padding: 8px 14px;
        border-radius: 8px 8px 0 0;
        border: 1px solid transparent;
        border-bottom: none;
        background: transparent;
        font-size: 14px;
        cursor: pointer;
        color: #4b5563;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }

    .profile-tab-btn.active {
        background: #ffffff;
        border-color: #e5e7eb;
        border-bottom-color: #ffffff;
        color: #111827;
        font-weight: 500;
    }

    .profile-tabs-spacer {
        flex: 1;
        border-bottom: 1px solid #e5e7eb;
    }

    .profile-tab-panels {
        flex: 1;
        overflow: auto;
        padding: 8px 2px 2px;
        min-height: 0;
        position: relative;
    }

    .profile-tab-panel {
        display: none;
        font-size: 14px;
        color: #111827;
        height: 100%;
    }

    .profile-tab-panel.active {
        display: block;
    }

    .profile-empty {
        padding: 12px;
        border-radius: 10px;
        background: #f9fafb;
        border: 1px dashed #e5e7eb;
        color: #6b7280;
        font-size: 14px;
    }

    /* Стили для вкладки настроек */
    .settings-tab-content {
        padding: 0;
        width: 95%;
        margin: 0 auto;
    }

    .settings-section {
        margin-bottom: 20px;
    }

    .settings-section:last-child {
        margin-bottom: 0;
    }

    .settings-section-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 20px 24px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .settings-section-card:hover {
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
    }

    .settings-section-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }

    .settings-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e5e7eb;
    }

    .settings-icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.2s;
    }

    .settings-icon-wrapper:hover {
        transform: scale(1.05);
    }

    .settings-icon-profile {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #ffffff;
    }

    .settings-icon-security {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: #ffffff;
    }

    .settings-icon-verification {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: #ffffff;
    }

    .settings-icon {
        width: 20px;
        height: 20px;
        stroke-width: 2.5;
    }

    .settings-card-enhanced {
        position: relative;
        overflow: hidden;
    }

    .settings-card-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .settings-card-enhanced:hover::before {
        opacity: 1;
    }

    .settings-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .settings-form-grid {
            grid-template-columns: 1fr;
        }
    }

    .settings-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .settings-form > div[style*="grid"] {
            grid-template-columns: 1fr !important;
        }

        .settings-section-header {
            flex-wrap: wrap;
        }

        .settings-icon-wrapper {
            width: 36px;
            height: 36px;
        }

        .settings-icon {
            width: 18px;
            height: 18px;
        }

        .settings-verification-benefits {
            padding: 12px;
        }
    }

    .settings-form-actions {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 8px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .settings-save-button {
        min-width: 140px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border: 1px solid #4f46e5;
        color: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
        text-decoration: none;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
    }

    .settings-save-button:hover {
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        border-color: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
    }

    .settings-save-button:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
    }

    .settings-save-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        background: #9ca3af;
        border-color: #9ca3af;
    }

    .settings-button-icon {
        width: 16px;
        height: 16px;
        stroke-width: 2.5;
    }

    .settings-button-verification {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border-color: #4facfe;
        box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3);
    }

    .settings-button-verification:hover {
        background: linear-gradient(135deg, #3d8bfe 0%, #00d9fe 100%);
        border-color: #3d8bfe;
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
    }

    .settings-action-message {
        color: #10b981;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .settings-action-message::before {
        content: "✓";
        font-size: 16px;
    }

    .settings-verification {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 20px 24px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .settings-verification:hover {
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
    }

    .settings-section-text {
        font-size: 14px;
        color: #6b7280;
        margin: 0 0 24px 0;
        line-height: 1.7;
    }

    .settings-verification-content {
        margin-bottom: 8px;
    }

    .settings-verification-benefits {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
        padding: 16px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .settings-benefit-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        color: #475569;
    }

    .settings-benefit-icon {
        width: 18px;
        height: 18px;
        color: #4facfe;
        flex-shrink: 0;
        stroke-width: 2.5;
    }

    .settings-card-verification {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .verification-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        background: #4f46e5;
        border: 1px solid #4f46e5;
        color: #ffffff;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, transform 0.1s;
        font-family: inherit;
        text-decoration: none;
    }

    .verification-button:hover {
        background: #4338ca;
        border-color: #4338ca;
        transform: translateY(-1px);
    }

    .verification-button:active {
        transform: translateY(0);
    }


    /* Стили для сетки домов */
.houses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 8px;
}

.house-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.house-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(15, 23, 42, 0.1);
}

.house-image {
    width: 100%;
    height: 180px;
    background: #f3f4f6;
    overflow: hidden;
    position: relative;
}

.house-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.house-image .photo-carousel {
    margin-top: 0;
}

.house-image .photos-viewport {
    height: 180px;
    border-radius: 12px 12px 0 0;
}

.house-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    background: #f3f4f6;
    color: #9ca3af;
}

.house-info {
    padding: 16px;
}

.house-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
}

.house-address {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 12px 0;
    line-height: 1.4;
}

.house-meta {
    display: flex;
    gap: 10px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.house-area {
    font-size: 14px;
    color: #374151;
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 6px;
}

.house-rent-type {
    font-size: 14px;
    color: #7c3aed;
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid #e9d5ff;
}

.house-coordinates {
    margin-bottom: 12px;
}

.house-coordinates small {
    font-size: 12px;
    color: #9ca3af;
}

.house-actions {
    display: flex;
    gap: 8px;
}

.btn-edit, .btn-delete {
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-edit {
    color: #374151;
}

.btn-edit:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.btn-delete {
    color: #dc2626;
    border-color: #fecaca;
}

.btn-delete:hover {
    background: #fef2f2;
    border-color: #fca5a5;
}
.profile-houses-header {
    display: flex;
    justify-content: flex-end; /* прижимаем кнопку вправо */
    margin-bottom: 12px;       /* отступ от кнопки до домов */
}


    .btn-primary,
    .btn-secondary {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        transition: background 0.2s, border-color 0.2s, transform 0.1s;
    }

    .btn-primary {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #4338ca;
        border-color: #4338ca;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #ffffff;
        border-color: #e5e7eb;
        color: #111827;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        transform: translateY(-1px);
    }

a {
    text-decoration: none;
}

@endsection


@section('main_content')
    @php
        $currentUser = auth()->user();
        // Используем методы модели для проверки прав
        $isOwner = $currentUser && $currentUser->canEditProfile($user);
        $canViewProfile = !$currentUser || $currentUser->canViewProfile($user);
    @endphp

    <div class="profile-wrapper">
        {{-- Шапка профиля --}}
        <div class="profile-header">
            <div class="profile-avatar">
                😊
            </div>
            <div class="profile-header-info">
                <div class="profile-name">
                    {{ trim(($user->name ?? '') . ' ' . ($user->sename ?? '')) ?: 'Пользователь #'.$user->user_id }}
                </div>
                <div class="profile-rating">
                    <span>Оценка пока отсутствует</span>
                </div>
            </div>
        </div>

        <div class="profile-layout">
            <aside class="profile-sidebar">
                <div class="profile-sidebar-top">
                    <p><strong>Роль:</strong> {{ $user->roles->name }}</p>
                    {{-- Показываем email только владельцу --}}
                    @if($isOwner)
                        <p><strong>Почта:</strong> {{ $user->email ?? 'не указан' }}</p>
                    @else
                        <p><strong>Почта:</strong> скрыта</p>
                    @endif
                </div>

                @if($isOwner)
                    <div class="profile-sidebar-bottom">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="profile-sidebar-button">
                                Выйти из аккаунта
                            </button>
                        </form>
                    </div>
                @elseif(auth()->check() && auth()->id() != $user->user_id)
                    <div class="profile-sidebar-bottom">
                        <a href="{{ route('chats.start', $user->user_id) }}" class="profile-sidebar-button" style="display: block; text-align: center; text-decoration: none; padding: 10px;">
                            Написать сообщение
                        </a>
                    </div>
                @endif
            </aside>

            <section class="profile-main">
                <div class="profile-tabs">
                    <button class="profile-tab-btn active" data-tab="houses" data-route="{{ route('profile.tab.houses', $user->user_id) }}">Дома</button>
                    @if($isOwner)
                        <button class="profile-tab-btn" data-tab="orders" data-route="{{ route('profile.tab.orders', $user->user_id) }}">Заказы</button>
                    @endif
                    @if($isOwner)
                        <button class="profile-tab-btn" data-tab="settings" data-route="{{ route('profile.tab.settings', $user->user_id) }}">Настройки</button>
                    @endif
                    <div class="profile-tabs-spacer"></div>
                </div>

                <div class="profile-tab-panels" data-user-id="{{ $user->user_id }}">
                    <div class="profile-tab-panel active" id="tab-houses">
                        @include('users.partials.houses-tab', ['houses' => $houses, 'isOwner' => $isOwner])
                    </div>
                    @if($isOwner)
                        <div class="profile-tab-panel" id="tab-orders">
                            {{-- Контент загружается через AJAX --}}
                        </div>
                    @endif
                    @if($isOwner)
                        <div class="profile-tab-panel" id="tab-settings">
                            @include('users.partials.settings-tab')
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <script>
        // Инициализация при загрузке страницы
        function initProfileTabs() {
            const buttons = document.querySelectorAll('.profile-tab-btn');
            const panels = document.querySelectorAll('.profile-tab-panel');
            const tabPanels = document.querySelector('.profile-tab-panels');
            const userId = tabPanels?.dataset.userId;
            
            if (!buttons.length || !panels.length) {
                return;
            }

            // Функция для определения активной вкладки из URL
            function getActiveTabFromURL() {
                const path = window.location.pathname;
                if (path.match(/\/tab\/settings/)) return 'settings';
                if (path.match(/\/tab\/orders/)) return 'orders';
                if (path.match(/\/tab\/houses/)) return 'houses';
                return 'houses'; // По умолчанию (если URL /profile/{id} без /tab)
            }

            // Функция для загрузки контента вкладки через AJAX
            async function loadTab(tab, route) {
                const panel = document.getElementById('tab-' + tab);
                if (!panel) return;

                // Показываем индикатор загрузки
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
                        // Инициализируем фото-карусели после загрузки контента
                        initPhotoCarousels(panel);
                        
                        // Инициализируем календари после загрузки контента
                        if (window.initHouseCalendars) {
                            window.initHouseCalendars();
                        }
                        
                        // Инициализируем фильтры заказов после загрузки контента
                        if (window.initOrdersFilters && tab === 'orders') {
                            // Пробуем несколько раз с задержкой
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
                        
                        // Инициализируем фильтры домов после загрузки контента
                        if (window.initHousesFilters && tab === 'houses') {
                            // Пробуем несколько раз с задержкой
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

            // Функция для переключения вкладки
            function switchTab(tab, route = null, skipLoad = false) {
                const btn = Array.from(buttons).find(b => b.dataset.tab === tab);
                if (!btn) return;

                const panel = document.getElementById('tab-' + tab);
                
                // Проверяем, есть ли уже загруженный контент в панели
                const hasContent = panel && (
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

                // Обновляем активные кнопки и панели
                buttons.forEach(b => b.classList.remove('active'));
                panels.forEach(panel => panel.classList.remove('active'));
                btn.classList.add('active');

                if (panel) {
                    panel.classList.add('active');
                }

                // Обновляем URL используя replaceState вместо pushState, чтобы не создавать лишние записи в истории
                // Это предотвращает проблемы при возврате назад
                if (route) {
                    window.history.replaceState({ tab, route }, '', route);
                }

                // Загружаем контент через AJAX только если его нет и не пропущена загрузка
                if (route && !skipLoad && !hasContent) {
                    loadTab(tab, route);
                } else if (hasContent && panel) {
                    // Если контент уже есть, просто инициализируем фото-карусели
                    initPhotoCarousels(panel);
                    // И инициализируем календари
                    if (window.initHouseCalendars) {
                        window.initHouseCalendars();
                    }
                    // Инициализируем фильтры заказов
                    if (window.initOrdersFilters && tab === 'orders') {
                        window.initOrdersFilters(panel);
                    }
                }
            }

            // Обработчик клика по вкладкам
            buttons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tab = btn.dataset.tab;
                    const route = btn.dataset.route;
                    switchTab(tab, route);
                });
            });

            // Обработчик события popstate (навигация назад/вперед)
            window.addEventListener('popstate', (e) => {
                // При возврате назад всегда перезагружаем страницу полностью, 
                // чтобы гарантировать загрузку полной структуры (как на странице редактирования дома)
                // Это предотвращает проблемы, когда отображается только контент вкладки
                const currentPath = window.location.pathname;
                
                // Проверяем наличие основных элементов страницы
                const profileWrapper = document.querySelector('.profile-wrapper');
                const profileHeader = document.querySelector('.profile-header');
                
                // Если нет основных элементов - значит страница не загружена полностью, перезагружаем
                if (!profileWrapper || !profileHeader) {
                    window.location.href = currentPath;
                    return;
                }
                
                // Если URL содержит /tab/, значит мы возвращаемся на вкладку
                // В этом случае перезагружаем страницу, чтобы загрузить её полностью
                if (currentPath && currentPath.includes('/tab/')) {
                    window.location.href = currentPath;
                    return;
                }
                
                // Для всех остальных случаев тоже перезагружаем
                window.location.reload();
            });

            // Проверяем наличие основных элементов профиля при первой загрузке
            const profileWrapper = document.querySelector('.profile-wrapper');
            const profileHeader = document.querySelector('.profile-header');
            
            // Если нет основных элементов профиля, страница не загружена полностью
            if (!profileWrapper || !profileHeader) {
                // Перезагружаем страницу, чтобы загрузить полную структуру
                window.location.reload();
                return;
            }
            
            // Определяем активную вкладку при первой загрузке
            const activeTab = getActiveTabFromURL();
            const activePanel = document.getElementById('tab-' + activeTab);
            
            // Проверяем, есть ли контент в активной панели
            const hasContent = activePanel && (
                activePanel.querySelector('.houses-grid') !== null ||
                activePanel.querySelector('.orders-houses-grid') !== null ||
                activePanel.querySelector('.orders-house-card') !== null ||
                activePanel.querySelector('.orders-compact-card') !== null ||
                activePanel.querySelector('.settings-tab-content') !== null ||
                (activePanel.querySelector('.profile-empty') !== null && 
                 !activePanel.innerHTML.includes('Загрузка...') &&
                 activePanel.textContent.trim() !== 'Загрузка...' &&
                 activePanel.textContent.trim() !== '')
            );
            
            if (activePanel) {
                // Устанавливаем активную вкладку
                const btn = Array.from(buttons).find(b => b.dataset.tab === activeTab);
                if (btn) {
                    buttons.forEach(b => b.classList.remove('active'));
                    panels.forEach(panel => panel.classList.remove('active'));
                    btn.classList.add('active');
                    activePanel.classList.add('active');
                }
                
                // Если контент уже есть на странице, просто инициализируем его
                if (hasContent) {
                    initPhotoCarousels(activePanel);
                    if (window.initHouseCalendars) {
                        window.initHouseCalendars();
                    }
                    // Инициализируем фильтры заказов
                    if (window.initOrdersFilters && activeTab === 'orders') {
                        window.initOrdersFilters(activePanel);
                    }
                } else {
                    // Если контента нет, загружаем его
                    if (btn && btn.dataset.route) {
                        loadTab(activeTab, btn.dataset.route);
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
        
        // Также инициализируем при полной загрузке страницы (для случаев возврата назад)
        window.addEventListener('load', () => {
            // Небольшая задержка, чтобы убедиться, что все элементы загружены
            setTimeout(initProfileTabs, 100);
        });
    </script>
@endsection

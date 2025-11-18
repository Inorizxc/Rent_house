@extends('layout')

@section('title')
    Профиль
@endsection

@section('style')
    body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f6f6f7;
    }

    .profile-wrapper {
        padding: 45px 12px 12px; /* отступ от шапки */
        max-width: 1400px;
        margin: 0 auto;
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
        min-height: 420px;
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
        max-height: calc(100vh - 140px);
    }

    .profile-tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 10px;
        gap: 4px;
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
    }

    .profile-tab-panel {
        display: none;
        font-size: 14px;
        color: #111827;
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

@endsection


@section('main_content')
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
                    <p><strong>ID:</strong> {{ $user->user_id }}</p>
                    <p><strong>Email:</strong> {{ $user->email ?? 'не указан' }}</p>
                </div>

                <div class="profile-sidebar-bottom">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="profile-sidebar-button">
                            Выйти из аккаунта
                        </button>
                    </form>
                </div>
            </aside>

            <section class="profile-main">
                <div class="profile-tabs">
                    <button class="profile-tab-btn active" data-tab="houses">Дома</button>
                    <button class="profile-tab-btn" data-tab="orders">Заказы</button>
                    <button class="profile-tab-btn" data-tab="settings">Настройки</button>
                    <div class="profile-tabs-spacer"></div>
                </div>

                <div class="profile-tab-panels">
                    <div class="profile-tab-panel active" id="tab-houses">
                        @if($houses->isEmpty())
                            <div class="profile-empty">
                                У пользователя пока нет опубликованных домов.
                            </div>
                        @else
                            <div class="houses-grid">
                                @foreach($houses as $house)
                                    <article class="house-card">
                                        @php
                                            $photoPayload = $house->photo
                                                ->filter(fn($photo) => !empty($photo->path))
                                                ->map(fn($photo) => [
                                                    'path' => $photo->path,
                                                    'name' => $photo->name,
                                                ])
                                                ->values();
                                        @endphp
                                        <div
                                            class="house-image"
                                            data-house-photos='@json($photoPayload)'
                                            data-empty-text="Нет фотографий"
                                        >
                                            @if($photoPayload->isNotEmpty())
                                                <img
                                                    src="{{ asset('storage/' . $photoPayload->first()['path']) }}"
                                                    alt="Фото дома #{{ $house->house_id }}"
                                                >
                                            @else
                                                <div class="house-image-placeholder">🏠</div>
                                            @endif
                                        </div>
                                        <div class="house-info">
                                            <h3 class="house-title">
                                                {{ $house->adress ?? 'Дом #'.$house->house_id }}
                                            </h3>
                                            <p class="house-address">
                                                {{ $house->adress ?? 'Адрес не указан' }}
                                            </p>
                                            <div class="house-meta">
                                                @if(!is_null($house->area))
                                                    <span class="house-area">
                                                        {{ number_format($house->area, 0, ',', ' ') }} м²
                                                    </span>
                                                @endif
                                                @if(optional($house->rent_type)->name)
                                                    <span class="house-rent-type">
                                                        {{ $house->rent_type->name }}
                                                    </span>
                                                @endif
                                                @if(optional($house->house_type)->name)
                                                    <span class="house-rent-type">
                                                        {{ $house->house_type->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="house-coordinates">
                                                <small>
                                                    Координаты:
                                                    {{ $house->lat ?? '—' }},
                                                    {{ $house->lng ?? '—' }}
                                                </small>
                                            </div>
                                            <div class="house-actions">
                                                <a class="btn-edit" href="{{ route('houses.show', $house->house_id) }}">
                                                    Просмотр
                                                </a>
                                                <a class="btn-edit" href="{{ route('houses.edit', $house->house_id) }}">
                                                    Редактировать
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="profile-tab-panel" id="tab-orders">
                        <div class="profile-empty">
                            Раздел заказов находится в разработке.
                        </div>
                    </div>
                    <div class="profile-tab-panel" id="tab-settings">
                        <div class="profile-empty">
                            Настройки профиля появятся позже.
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('.profile-tab-btn');
            const panels = document.querySelectorAll('.profile-tab-panel');

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const tab = btn.dataset.tab;

                    buttons.forEach(b => b.classList.remove('active'));
                    panels.forEach(panel => panel.classList.remove('active'));
                    btn.classList.add('active');
                    document.getElementById('tab-' + tab).classList.add('active');
                });
            });

            const photoBlocks = document.querySelectorAll('[data-house-photos]');
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
        });
    </script>
@endsection

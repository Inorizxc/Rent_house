@php
    $houses = $user->house ?? collect();
    $currentUser = auth()->user();
    $canCreateHouse = $currentUser && $currentUser->canCreateHouse();
@endphp

<div class="orders-tab-content">
    @if(isset($isOwner) && $isOwner && $canCreateHouse)
        <div class="orders-header">
            <a href="{{ route('houses.create') }}" class="btn-primary">
                Создать
            </a>
        </div>
    @endif

    @if($houses->isEmpty())
        <div class="profile-empty">
            У вас пока нет домов.
        </div>
    @else
        <div class="orders-houses-grid">
        @foreach($houses as $house)
            <div class="orders-house-card">
                <div class="orders-house-header">
                    <div class="orders-house-title">{{ $house->adress ?? 'Дом #' . $house->house_id }}</div>
                    <div class="orders-house-subtitle">Дом #{{ $house->house_id }}</div>
                </div>

                @php
                    $photoPayload = $house->photo
                        ->filter(fn($photo) => !empty($photo->path))
                        ->map(fn($photo) => [
                            'path' => $photo->path,
                            'name' => $photo->name,
                        ])
                        ->values();
                @endphp

                <div class="orders-house-photos">
                    <div
                        class="orders-house-image"
                        data-house-photos='@json($photoPayload)'
                        data-empty-text="Нет фотографий"
                    >
                        @if($photoPayload->isNotEmpty())
                            <img
                                src="{{ asset('storage/' . $photoPayload->first()['path']) }}"
                                alt="Фото дома #{{ $house->house_id }}"
                            >
                        @else
                            <div class="orders-house-image-placeholder">🏠</div>
                        @endif
                    </div>
                </div>

                <div class="orders-house-section">
                    <div class="settings-section-title">О доме</div>
                    <div class="orders-house-description">
                        <div class="description-row">
                            <div class="description-label">Адрес</div>
                            <div class="description-value">{{ $house->adress ?? '—' }}</div>
                        </div>
                        <div class="description-row">
                            <div class="description-label">Площадь</div>
                            <div class="description-value">
                                {{ $house->area ? number_format($house->area, 0, ',', ' ') . ' м²' : '—' }}
                            </div>
                        </div>
                        <div class="description-row">
                            <div class="description-label">Тип аренды</div>
                            <div class="description-value">
                                {{ optional($house->rent_type)->name ?? '—' }}
                            </div>
                        </div>
                        <div class="description-row">
                            <div class="description-label">Тип дома</div>
                            <div class="description-value">
                                {{ optional($house->house_type)->name ?? '—' }}
                            </div>
                        </div>
                        <div class="description-row">
                            <div class="description-label">Координаты</div>
                            <div class="description-value">
                                {{ $house->lat && $house->lng ? $house->lat . ', ' . $house->lng : '—' }}
                            </div>
                        </div>
                        <div class="description-row">
                            <div class="description-label">Стоимость</div>
                            <div class="description-value">
                                @if($house->price_id)
                                    {{ number_format($house->price_id, 0, ',', ' ') }} ₽
                                @else
                                    Не указана
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="orders-house-actions">
                    <a href="{{ route('houses.show', $house->house_id) }}" class="btn-primary">
                        Просмотр
                    </a>
                    <a href="{{ route('houses.edit', $house->house_id) }}" class="btn-secondary">
                        Редактировать
                    </a>
                </div>
            </div>
        @endforeach
        </div>
    @endif
</div>


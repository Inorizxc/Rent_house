@php
    $currentUser = auth()->user();
    $canCreateHouse = $currentUser && $currentUser->canCreateHouse();
@endphp

@if(isset($isOwner) && $isOwner && $canCreateHouse)
    <div class="profile-houses-header">
        <a href="{{ route('houses.create') }}" class="btn-edit">
            Создать
        </a>
    </div>
@endif
@if(!isset($houses) || $houses->isEmpty())
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
                        @php
                            $canEditThisHouse = $currentUser && $currentUser->canEditHouse($house);
                        @endphp
                        @if($canEditThisHouse)
                            <a class="btn-edit" href="{{ route('houses.edit', $house->house_id) }}">
                                Редактировать
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif


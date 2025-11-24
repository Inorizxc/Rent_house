
<div>
    @if (session()->has('ok'))
        <div class="alert-success">
            {{ session('ok') }}
        </div>
    @endif

    <div class="search-section">
        <div class="search-controls">
            <input type="text"
                   wire:model.defer="searchInput"
                   wire:keydown.enter="applySearch"
                   placeholder="Поиск по адресу/площади"
                   class="search-input">
            <button wire:click="applySearch" class="btn-search">Искать</button>

            @if($search)
                <button class="btn-reset"
                        wire:click="$set('searchInput',''); $set('search','');">Сброс</button>
            @endif
        </div>

        {{-- создание теперь через контроллер --}}
        @php
            $currentUser = auth()->user();
            $canCreateHouse = $currentUser && $currentUser->canCreateHouse();
        @endphp
        @if($canCreateHouse)
            <a href="{{ route('houses.create') }}" class="btn-add-house">
                Добавить дом
            </a>
        @endif
    </div>

    {{-- Сетка карточек --}}
    <div class="houses-grid">
        @forelse($houses as $h)
            <div class="house-card" wire:key="card-{{ $h->house_id }}">
                <a href="{{ route('houses.show', $h) }}">
                    <img src="{{ $h->image_url }}" alt="Фото дома #{{ $h->house_id }}"
                         class="house-card-image">

                    <div class="house-card-content">
                        <div class="house-card-header">
                            <h3 class="house-card-title">{{ $h->adress }}</h3>
                            @if($h->is_deleted)
                                <span class="house-card-badge">Удалён</span>
                            @endif
                        </div>

                    <dl class="house-card-details">
                        <div>
                            <dt>Площадь</dt>
                            <dd>{{ $h->area ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Цена ID</dt>
                            <dd>{{ $h->price_id ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Тип аренды</dt>
                            <dd>{{ $h->rent_type_id ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Тип дома</dt>
                            <dd>{{ $h->house_type_id ?? '—' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt>Координаты</dt>
                            <dd class="font-mono">{{ $h->lat }}, {{ $h->lng }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt>Пользователь</dt>
                            <dd>
                                @if($h->user)
                                    {{ trim(($h->user->sename ?? '').' '.($h->user->name ?? '').' '.($h->user->patronymic ?? '')) ?: ('User #'.$h->user_id) }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                    </div>
                </a>

                @php
                    $canEditThisHouse = $currentUser && $currentUser->canEditHouse($h);
                    $canDeleteThisHouse = $currentUser && $currentUser->canDeleteHouse($h);
                @endphp

                @if($canEditThisHouse || $canDeleteThisHouse)
                    <div class="house-card-actions">
                        @if($canEditThisHouse)
                            <a href="{{ route('houses.edit', $h) }}" class="btn-edit">Редактировать</a>
                        @endif

                        @if($canDeleteThisHouse)
                            <form method="POST" action="{{ route('houses.destroy', $h) }}"
                                  onsubmit="return confirm('Удалить дом #{{ $h->house_id }}?')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    Удалить
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="houses-empty">
                Записей нет
            </div>
        @endforelse
    </div>

    <div class="pagination-wrapper">
        {{ $houses->links() }}
    </div>
</div>

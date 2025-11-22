

<div class="p-6">
    @if (session()->has('ok'))
        <div class="mb-4 rounded border px-4 py-2 bg-green-50 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-2">
            <input type="text"
                   wire:model.defer="searchInput"
                   wire:keydown.enter="applySearch"
                   placeholder="Поиск по адресу/площади"
                   class="border rounded px-3 py-2 w-72">
            <button wire:click="applySearch" class="px-4 py-2 border rounded">Искать</button>

            @if($search)
                <button class="px-3 py-2 text-sm underline"
                        wire:click="$set('searchInput',''); $set('search','');">Сброс</button>
            @endif
        </div>

        {{-- создание теперь через контроллер --}}
        @php
            $currentUser = auth()->user();
            $canCreateHouse = $currentUser && $currentUser->canCreateHouse();
        @endphp
        @if($canCreateHouse)
            <a href="{{ route('houses.create') }}" class="px-4 py-2 rounded bg-blue-600 text-white">
                Добавить дом
            </a>
        @endif
    </div>

    {{-- Сетка карточек --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($houses as $h)
            <div class="rounded-xl border bg-white shadow overflow-hidden flex flex-col" wire:key="card-{{ $h->house_id }}">
                <div class="aspect-[4/3] bg-gray-100">
                    <img src="{{ $h->image_url }}" alt="Фото дома #{{ $h->house_id }}"
                         class="w-full h-full object-cover">
                </div>

                <div class="p-4 flex-1 flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold truncate">{{ $h->adress }}</h3>
                        @if($h->is_deleted)
                            <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">Удалён</span>
                        @endif
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-gray-600">
                        <div>
                            <dt class="text-gray-500">Площадь</dt>
                            <dd>{{ $h->area ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Цена ID</dt>
                            <dd>{{ $h->price_id ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Тип аренды</dt>
                            <dd>{{ $h->rent_type_id ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Тип дома</dt>
                            <dd>{{ $h->house_type_id ?? '—' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Координаты</dt>
                            <dd class="font-mono text-xs">{{ $h->lat }}, {{ $h->lng }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Пользователь</dt>
                            <dd>
                                @if($h->user)
                                    {{ trim(($h->user->sename ?? '').' '.($h->user->name ?? '').' '.($h->user->patronymic ?? '')) ?: ('User #'.$h->user_id) }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>

                    @php
                        $canEditThisHouse = $currentUser && $currentUser->canEditHouse($h);
                        $canDeleteThisHouse = $currentUser && $currentUser->canDeleteHouse($h);
                    @endphp
                    @if($canEditThisHouse || $canDeleteThisHouse)
                        <div class="mt-4 flex items-center justify-between gap-2">
                            @if($canEditThisHouse)
                                <a href="{{ route('houses.edit', $h) }}" class="px-3 py-2 border rounded">Редактировать</a>
                            @endif

                            @if($canDeleteThisHouse)
                                <form method="POST" action="{{ route('houses.destroy', $h) }}"
                                      onsubmit="return confirm('Удалить дом #{{ $h->house_id }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2 border rounded text-red-600">
                                        Удалить
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 py-10">
                Записей нет
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $houses->links() }}
    </div>
</div>

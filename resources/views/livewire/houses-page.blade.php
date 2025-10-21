{{-- resources/views/livewire/houses-page.blade.php --}}
<div class="users-page max-w-[1500px] mx-auto p-6">

    {{-- Панель управления: поиск + создание --}}
    <div class="toolbar flex items-center justify-between mb-4 gap-4">
        <div class="flex gap-2 w-full">
            <input
                type="text"
                wire:model.defer="searchInput"
                class="input flex-grow"
                placeholder="Поиск домов (адрес, площадь)…"
            />
            <button class="btn btn-primary" wire:click="applySearch">Поиск</button>
            <button class="btn btn-accent" wire:click="startCreate">+ Новый дом</button>
        </div>
    </div>

    {{-- Сообщение об успехе (закрываемое) --}}
    @if (session('ok'))
        <div x-data="{ open: true }" x-show="open" class="alert alert-success mb-3 relative pr-10">
            {{ session('ok') }}
            <button
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 btn btn-ghost px-2 py-1"
                aria-label="Закрыть"
                @click="open = false"
            >✕</button>
        </div>
    @endif

    {{-- Форма создания/редактирования --}}
    @if(!is_null($editingId))
        <div class="card mb-6 p-5">
            <div class="section-title mb-3">
                {{ $editingId === 0 ? 'Создание дома' : 'Редактирование дома' }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Левая часть формы --}}
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Адрес</label>
                        <input type="text" class="input" wire:model.defer="form.adress">
                        @error('form.adress') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Площадь (м²)</label>
                        <input type="number" step="0.01" class="input" wire:model.defer="form.area">
                        @error('form.area') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Тип аренды (rent_type_id)</label>
                        <input type="number" class="input" wire:model.defer="form.rent_type_id">
                        @error('form.rent_type_id') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Тип дома (house_type_id)</label>
                        <input type="number" class="input" wire:model.defer="form.house_type_id">
                        @error('form.house_type_id') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Пользователь (user_id)</label>
                        <select class="input" wire:model.defer="form.user_id">
                            <option value="">— не выбран —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->user_id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('form.user_id') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Цена (price_id)</label>
                        <input type="number" class="input" wire:model.defer="form.price_id" placeholder="Например 15000">
                        @error('form.price_id') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Календарь (calendar_id)</label>
                        <input type="number" class="input" wire:model.defer="form.calendar_id">
                        @error('form.calendar_id') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Долгота (lng)</label>
                        <input type="number" step="0.000001" class="input" wire:model.defer="form.lng">
                        @error('form.lng') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Широта (lat)</label>
                        <input type="number" step="0.000001" class="input" wire:model.defer="form.lat">
                        @error('form.lat') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Статус</label>
                        <select class="input" wire:model.defer="form.is_deleted">
                            <option value="0">Активен</option>
                            <option value="1">Удалён</option>
                        </select>
                        @error('form.is_deleted') <div class="err">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Правая часть: превью + загрузка --}}
                <div class="space-y-3">
                    <label class="block mb-1 text-sm text-gray-400">Изображение</label>

                    {{-- Превью (иконка, если фото нет) --}}
                    <div class="rounded-xl overflow-hidden border border-[#2a2a2a] bg-[#0f0f0f]">
                        @php
                            $img = null;
                            if (!is_null($editingId)) {
                                $try = ['jpg','jpeg','png','webp','gif'];
                                foreach ($try as $ext) {
                                    $p = public_path('storage/houses/'.$editingId.'.'.$ext);
                                    if (file_exists($p)) { $img = asset('storage/houses/'.$editingId.'.'.$ext); break; }
                                }
                            }
                        @endphp
                        @if($imageTmp)
                            <img src="{{ $imageTmp->temporaryUrl() }}" class="w-full h-48 object-cover" alt="">
                        @elseif($img)
                            <img src="{{ $img }}" class="w-full h-48 object-cover" alt="">
                        @else
                            <div class="w-full h-48 flex items-center justify-center text-[#1DB954]/80">
                                {{-- Иконка "Нет фото" --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <path d="M3 15l4-4 3 3 5-5 6 6"></path>
                                    <circle cx="9" cy="7.5" r="1.5"></circle>
                                </svg>
                                <span class="ml-2 text-sm text-gray-400">Нет фото</span>
                            </div>
                        @endif
                    </div>

                    <input type="file" class="input" wire:model="imageTmp" accept="image/*">
                    @error('imageTmp') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <button class="btn btn-success" wire:click="save">Сохранить</button>
                <button class="btn btn-ghost" wire:click="cancelEdit">Отмена</button>
            </div>
        </div>
    @endif

    {{-- Грид карточек домов --}}
<div class="houses-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-4">

    @forelse($houses as $h)
        @php
            $img = null;
            $try = ['jpg','jpeg','png','webp','gif'];
            foreach ($try as $ext) {
                $p = public_path('storage/houses/'.$h->house_id.'.'.$ext);
                if (file_exists($p)) { $img = asset('storage/houses/'.$h->house_id.'.'.$ext); break; }
            }
        @endphp

        <article class="card overflow-hidden bg-[#121212] border border-[#2a2a2a] rounded-xl shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
            {{-- Фото или иконка --}}
            @if($img)
                <img src="{{ $img }}" alt="Дом #{{ $h->house_id }}" class="w-full h-44 object-cover">
            @else
                <div class="w-full h-44 flex items-center justify-center bg-[#181818] border-b border-[#2a2a2a] text-[#1DB954]/80">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <path d="M3 15l4-4 3 3 5-5 6 6"></path>
                        <circle cx="9" cy="7.5" r="1.5"></circle>
                    </svg>
                </div>
            @endif

            <div class="p-4">
                {{-- Заголовок + цена --}}
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-semibold text-white leading-snug line-clamp-2">
                        {{ $h->adress ?: 'Адрес не указан' }}
                    </h2>

                    
                </div>

                {{-- Метаданные строкой с иконками --}}
                <ul class="mt-3 space-y-1.5 text-[13px] text-gray-300">
                    @if(!is_null($h->area))
                        <li class="flex items-center gap-2">
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded bg-[#1E1E1E] border border-[#2a2a2a]">㎡</span>
                            <span class="text-gray-400">Площадь:</span>
                            <span class="font-medium text-gray-200">
                                {{ is_numeric($h->area)
                                    ? rtrim(rtrim(number_format((float)$h->area, 2, '.', ' '), '0'), '.')
                                    : $h->area
                                }} м²
                            </span>
                        </li>
                    @endif

                    @if($h->house_type)
                        <li class="flex items-center gap-2">
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded bg-[#1E1E1E] border border-[#2a2a2a]">🏠</span>
                            <span class="text-gray-400">Тип дома:</span>
                            <span class="font-medium text-gray-200">{{ $h->house_type->name ?? $h->house_type_id }}</span>
                        </li>
                    @endif

                    @if($h->rent_type)
                        <li class="flex items-center gap-2">
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded bg-[#1E1E1E] border border-[#2a2a2a]">⏱</span>
                            <span class="text-gray-400">Аренда:</span>
                            <span class="font-medium text-gray-200">{{ $h->rent_type->name ?? $h->rent_type_id }}</span>
                        </li>
                    @endif
                    
                    @if(is_numeric($h->price_id))
                        <li class="flex items-center gap-2">
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded bg-[#1E1E1E] border border-[#2a2a2a]">💸</span>
                            <span class="text-gray-400">Цена:</span>
                            {{ number_format((int)$h->price_id, 0, '.', ' ') }} ₽
                        </li>
                    @endif


                    @if($h->lng && $h->lat)
                        <li class="flex items-center gap-2">
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded bg-[#1E1E1E] border border-[#2a2a2a]">📍</span>
                            <span class="font-medium text-gray-200">{{ $h->lat }}, {{ $h->lng }}</span>
                        </li>
                    @endif
                </ul>

                {{-- Чипы + действия --}}
                <div class="mt-3 flex items-center justify-between">
                    <div class="flex flex-wrap gap-2 text-[11px]">
                        @if($h->user)
                            @php
                                $fio = trim(implode(' ', array_filter([
                                    $h->user->sename ?? '',
                                    $h->user->name ?? '',
                                    $h->user->patronymic ?? '',
                                ])));
                            @endphp
                            <span class="px-2 py-1 rounded-full bg-[#1E1E1E] border border-[#2a2a2a] text-gray-300">
                                🙋 {{ $fio ?: 'Без имени' }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a class="btn btn-accent no-underline hover:no-underline text-sm" href="#" role="button">Подробнее</a>
                        <button class="btn btn-primary" wire:click="startEdit({{ $h->house_id }})">✏️</button>
                        <button class="btn btn-ghost text-red-500"
                                onclick="return confirm('Удалить дом?')"
                                wire:click="delete({{ $h->house_id }})">🗑️</button>
                    </div>
                </div>
            </div>
        </article>

    @empty
        <div class="col-span-full">
            <div class="card p-6 text-center text-gray-400 bg-[#181818] border border-[#2a2a2a] rounded-xl">
                Домов пока нет. Добавь первый дом!
            </div>
        </div>
    @endforelse
</div>

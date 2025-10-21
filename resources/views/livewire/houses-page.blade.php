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

    {{-- Сообщение об успехе --}}
    @if (session('ok'))
        <div class="alert alert-success mb-3">
            {{ session('ok') }}
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
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

        @forelse($houses as $h)
            @php
                // пробуем найти физический файл: storage/houses/{id}.{ext}
                $img = null;
                $try = ['jpg','jpeg','png','webp','gif'];
                foreach ($try as $ext) {
                    $p = public_path('storage/houses/'.$h->house_id.'.'.$ext);
                    if (file_exists($p)) { $img = asset('storage/houses/'.$h->house_id.'.'.$ext); break; }
                }
            @endphp

            <div class="card overflow-hidden">
                {{-- Фото или иконка --}}
                @if($img)
                    <img src="{{ $img }}" alt="Дом #{{ $h->house_id }}" class="w-full h-44 object-cover">
                @else
                    <div class="w-full h-44 flex items-center justify-center bg-[#0f0f0f] border-b border-[#2a2a2a] text-[#1DB954]/80">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <path d="M3 15l4-4 3 3 5-5 6 6"></path>
                            <circle cx="9" cy="7.5" r="1.5"></circle>
                        </svg>
                        <span class="ml-2 text-sm text-gray-400">Нет фото</span>
                    </div>
                @endif

                <div class="p-4">
                    <div class="text-lg font-extrabold mb-1">
                        {{ $h->adress ?: 'Адрес не указан' }}
                    </div>

                    <div class="text-sm text-gray-400 mb-2">
                        @if($h->house_type) Тип: {{ $h->house_type->name ?? $h->house_type_id }} • @endif
                        @if($h->rent_type) Аренда: {{ $h->rent_type->name ?? $h->rent_type_id }} • @endif
                        @if(!is_null($h->area))
                            Площадь:
                            {{ is_numeric($h->area)
                                ? rtrim(rtrim(number_format((float)$h->area, 2, '.', ' '), '0'), '.')
                                : $h->area
                            }} м²
                        @endif
                    </div>

                    @if($h->lng && $h->lat)
                        <div class="text-xs text-gray-500">Координаты: {{ $h->lat }}, {{ $h->lng }}</div>
                    @endif

                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-300">
                        @if($h->user)
                            <span class="px-2 py-1 rounded-full bg-[#1E1E1E] border border-[#2a2a2a]">🙋 {{ $h->user->name }}</span>
                        @endif
                        <span class="px-2 py-1 rounded-full bg-[#1E1E1E] border border-[#2a2a2a]">ID: {{ $h->house_id }}</span>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <button class="btn btn-accent" disabled>Заказать</button>
                        <div class="flex gap-2">
                            <button class="btn btn-primary" wire:click="startEdit({{ $h->house_id }})">✏️</button>
                            <button class="btn btn-ghost text-red-500"
                                    onclick="return confirm('Удалить дом?')"
                                    wire:click="delete({{ $h->house_id }})">🗑️</button>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="col-span-full">
                <div class="card p-6 text-center text-gray-400">Домов пока нет. Добавь первый дом!</div>
            </div>
        @endforelse

    </div>

    {{-- Пагинация --}}
    <div class="mt-6 pagination-wrap">
        {{ $houses->links() }}
    </div>
</div>

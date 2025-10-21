{{-- resources/views/livewire/users-page.blade.php --}}
<div class="users-page max-w-[1500px] mx-auto p-6">

    {{-- Панель управления: поиск + создание --}}
    <div class="flex items-center justify-between mb-4 gap-4">
        <div class="flex gap-2 w-full">
            <input
                type="text"
                wire:model.defer="searchInput"
                class="input flex-grow"
                placeholder="Поиск (имя, email, телефон)…"
            />

            <button class="btn btn-primary" wire:click="applySearch">
                Поиск
            </button>

            <button class="btn btn-accent" wire:click="startCreate">
                Создать пользователя
            </button>
        </div>
    </div>

    {{-- Сообщение об успехе --}}
    @if (session('ok'))
        <div class="alert alert-success mb-3">
            {{ session('ok') }}
        </div>
    @endif

    {{-- Карточка-обёртка таблицы --}}
    <div class="overflow-x-auto card">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Роль</th>
                    <th>Имя</th>
                    <th>Фамилия</th>
                    <th>Отчество</th>
                    <th>Дата рождения</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Карта</th>
                    <th>Пароль</th>
                    <th class="w-40">Действие</th>
                </tr>
            </thead>

            <tbody>
                {{-- Новая строка (режим создания) --}}
                @if($editingRowId === 0)
                    <tr class="is-editing">
                        <td>—</td>

                        {{-- РОЛЬ --}}
                        <td>
                            <select
                                wire:model.defer="row.role_id"
                                wire:keydown.enter="saveRow"
                                wire:blur="saveRow"
                                autofocus
                                class="input w-full"
                            >
                                <option value="">— выбери —</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->role_id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </td>

                        {{-- ИМЯ --}}
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.name"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        {{-- ФАМИЛИЯ --}}
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.sename"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        {{-- ОТЧЕСТВО --}}
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.patronymic"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        {{-- ДАТА РОЖДЕНИЯ --}}
                        <td>
                            <input
                                type="date"
                                wire:model.defer="row.birth_date"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                            @error('row.birth_date') <div class="err">{{ $message }}</div> @enderror
                        </td>

                        {{-- EMAIL --}}
                        <td>
                            <input
                                type="email"
                                wire:model.defer="row.email"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        {{-- ТЕЛЕФОН --}}
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.phone"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        {{-- КАРТА --}}
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.card"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        {{-- ПАРОЛЬ (обязателен при создании) --}}
                        <td>
                            <input
                                type="password"
                                wire:model.defer="passwordNew"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                                placeholder="Новый пароль"
                            />
                        </td>

                        <td>
                            <button class="btn btn-success" wire:click="saveRow">Создать</button>
                            <button class="btn btn-ghost" wire:click="cancelEdit">Отмена</button>
                        </td>
                    </tr>
                @endif

                {{-- Существующие пользователи --}}
                @foreach ($users as $u)
                    @php $isRow = $editingRowId === $u->user_id; @endphp
                    <tr class="{{ $isRow ? 'is-editing' : '' }}">
                        <td>{{ $u->user_id }}</td>

                        {{-- РОЛЬ --}}
                        <td>
                            @if($isRow && $editingField==='role_id')
                                <select
                                    wire:model.defer="row.role_id"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                >
                                    @foreach($roles as $r)
                                        <option value="{{ $r->role_id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="cell-action"
                                     wire:click="setField({{ $u->user_id }}, 'role_id')">
                                    {{ $isRow
                                        ? (optional($roles->firstWhere('role_id',$row['role_id']))->name)
                                        : ($u->roles->name ?? '—') }}
                                </div>
                            @endif
                        </td>

                        {{-- ИМЯ --}}
                        <td>
                            @if($isRow && $editingField==='name')
                                <input
                                    type="text"
                                    wire:model.defer="row.name"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                                @error('row.name') <div class="err">{{ $message }}</div> @enderror
                            @else
                                <div class="cell-action" wire:click="setField({{ $u->user_id }}, 'name')">
                                    {{ $isRow ? $row['name'] : $u->name }}
                                </div>
                            @endif
                        </td>

                        {{-- ФАМИЛИЯ --}}
                        <td>
                            @if($isRow && $editingField==='sename')
                                <input
                                    type="text"
                                    wire:model.defer="row.sename"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                            @else
                                <div class="cell-action" wire:click="setField({{ $u->user_id }}, 'sename')">
                                    {{ $isRow ? $row['sename'] : $u->sename }}
                                </div>
                            @endif
                        </td>

                        {{-- ОТЧЕСТВО --}}
                        <td>
                            @if($isRow && $editingField==='patronymic')
                                <input
                                    type="text"
                                    wire:model.defer="row.patronymic"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                            @else
                                <div class="cell-action" wire:click="setField({{ $u->user_id }}, 'patronymic')">
                                    {{ $isRow ? $row['patronymic'] : $u->patronymic }}
                                </div>
                            @endif
                        </td>

                        {{-- ДАТА РОЖДЕНИЯ --}}
                        <td>
                            @if($isRow && $editingField==='birth_date')
                                <input
                                    type="date"
                                    wire:model.defer="row.birth_date"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                                @error('row.birth_date') <div class="err">{{ $message }}</div> @enderror
                            @else
                                <div class="cell-action" wire:click="setField({{ $u->user_id }}, 'birth_date')">
                                    {{ $isRow ? $row['birth_date'] : optional($u->birth_date)->format('Y-m-d') }}
                                </div>
                            @endif
                        </td>

                        {{-- EMAIL --}}
                        <td>
                            @if($isRow && $editingField==='email')
                                <input
                                    type="email"
                                    wire:model.defer="row.email"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                                @error('row.email') <div class="err">{{ $message }}</div> @enderror
                            @else
                                <div class="cell-action" wire:click="setField({{ $u->user_id }}, 'email')">
                                    {{ $isRow ? $row['email'] : $u->email }}
                                </div>
                            @endif
                        </td>

                        {{-- ТЕЛЕФОН --}}
                        <td>
                            @if($isRow && $editingField==='phone')
                                <input
                                    type="text"
                                    wire:model.defer="row.phone"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                            @else
                                <div class="cell-action" wire:click="setField({{ $u->user_id }}, 'phone')">
                                    {{ $isRow ? $row['phone'] : $u->phone }}
                                </div>
                            @endif
                        </td>

                        {{-- КАРТА --}}
                        <td>
                            @if($isRow && $editingField==='card')
                                <input
                                    type="text"
                                    wire:model.defer="row.card"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                            @else
                                <div class="cell-action" wire:click="setField({{ $u->user_id }}, 'card')">
                                    {{ $isRow ? $row['card'] : $u->card }}
                                </div>
                            @endif
                        </td>

                        {{-- ПАРОЛЬ (задаём новый) --}}
                        <td>
                            @if($isRow && $editingField==='password')
                                <input
                                    type="password"
                                    wire:model.defer="passwordNew"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                    placeholder="Новый пароль"
                                />
                                @error('passwordNew') <div class="err">{{ $message }}</div> @enderror
                            @else
                                <div class="cell-action text-blue-500"
                                     wire:click="setField({{ $u->user_id }}, 'password')">
                                    задать
                                </div>
                            @endif
                        </td>

                        {{-- ДЕЙСТВИЕ --}}
                        <td>
                            @if(!$isRow)
                                <button
                                    class="btn btn-ghost text-red-600"
                                    wire:click="deleteUser({{ $u->user_id }})"
                                    onclick="return confirm('Удалить пользователя?')"
                                >
                                    🗑️
                                </button>
                            @else
                                <button class="btn btn-success" wire:click="saveRow">Сохранить</button>
                                <button class="btn btn-ghost" wire:click="cancelEdit">Отмена</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Пагинация --}}
    <div class="mt-3 pagination-wrap">
        {{ $users->links() }}
    </div>
</div>

<div class="max-w-[1500px] mx-auto p-6">
    <div class="flex items-center justify-between mb-4 gap-4">
        <input type="text" wire:model.debounce.400ms="search"
               class="border rounded px-3 py-2 w-full"
               placeholder="Поиск (имя, email, телефон)…">
        <button class="bg-indigo-600 text-white rounded px-4 py-2" wire:click="startCreate">
            + Новый
        </button>
    </div>

    @if (session('ok'))
        <div class="bg-green-100 text-green-800 px-3 py-2 rounded mb-3">
            {{ session('ok') }}
        </div>
    @endif

    <div class="overflow-x-auto">
    <table class="min-w-full border text-sm">
        <thead class="bg-gray-50">
        <tr>
            <th class="p-2 border">ID</th>
            <th class="p-2 border">Роль</th>
            <th class="p-2 border">Имя</th>
            <th class="p-2 border">Фамилия</th>
            <th class="p-2 border">Отчество</th>
            <th class="p-2 border">Дата рождения</th>
            <th class="p-2 border">Email</th>
            <th class="p-2 border">Телефон</th>
            <th class="p-2 border">Карта</th>
            <th class="p-2 border">Пароль</th>
            <th class="p-2 border w-40">Действия</th>
        </tr>
        </thead>

        <tbody>
        {{-- Новая строка (если создаём) --}}
        @if($editingRowId === 0)
            <tr class="border-t bg-yellow-50">
                <td class="p-2 border">—</td>

                {{-- РОЛЬ --}}
                <td class="p-2 border">
                    <select wire:model.defer="row.role_id"
                            wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                            class="border rounded px-2 py-1 w-full">
                        <option value="">— выбери —</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->role_id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                    @error('row.role_id')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </td>

                {{-- ИМЯ --}}
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.name"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                    @error('row.name')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </td>

                {{-- ФАМИЛИЯ --}}
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.sename"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                </td>

                {{-- ОТЧЕСТВО --}}
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.patronymic"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                </td>

                {{-- ДАТА РОЖДЕНИЯ --}}
                <td class="p-2 border">
                    <input type="date" wire:model.defer="row.birth_date"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                    @error('row.birth_date')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </td>

                {{-- EMAIL --}}
                <td class="p-2 border">
                    <input type="email" wire:model.defer="row.email"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                    @error('row.email')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </td>

                {{-- ТЕЛЕФОН --}}
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.phone"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                </td>

                {{-- КАРТА --}}
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.card"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                </td>

                {{-- ПАРОЛЬ (обязателен при создании) --}}
                <td class="p-2 border">
                    <input type="password" wire:model.defer="passwordNew"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full"
                           placeholder="Новый пароль">
                    @error('passwordNew')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </td>

                <td class="p-2 border">
                    <button class="bg-green-600 text-white px-3 py-1 rounded" wire:click="saveRow">Создать</button>
                    <button class="ml-2 border px-3 py-1 rounded" wire:click="cancelEdit">Отмена</button>
                </td>
            </tr>
        @endif

        {{-- Существующие пользователи --}}
        @foreach ($users as $u)
            @php $isRow = $editingRowId === $u->user_id; @endphp
            <tr class="border-t hover:bg-gray-50">
                <td class="p-2 border">{{ $u->user_id }}</td>

                {{-- РОЛЬ --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='role_id')
                        <select wire:model.defer="row.role_id"
                                wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                                class="border rounded px-2 py-1 w-full">
                            @foreach($roles as $r)
                                <option value="{{ $r->role_id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('row.role_id')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                    @else
                        <div class="cursor-pointer"
                             wire:click="setField({{ $u->user_id }}, 'role_id')">
                            {{ $isRow ? (optional($roles->firstWhere('role_id',$row['role_id']))->name) : ($u->roles->name ?? '—') }}
                        </div>
                    @endif
                </td>

                {{-- ИМЯ --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='name')
                        <input type="text" wire:model.defer="row.name"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                        @error('row.name')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                    @else
                        <div class="cursor-pointer" wire:click="setField({{ $u->user_id }}, 'name')">
                            {{ $isRow ? $row['name'] : $u->name }}
                        </div>
                    @endif
                </td>

                {{-- ФАМИЛИЯ --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='sename')
                        <input type="text" wire:model.defer="row.sename"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                    @else
                        <div class="cursor-pointer" wire:click="setField({{ $u->user_id }}, 'sename')">
                            {{ $isRow ? $row['sename'] : $u->sename }}
                        </div>
                    @endif
                </td>

                {{-- ОТЧЕСТВО --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='patronymic')
                        <input type="text" wire:model.defer="row.patronymic"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                    @else
                        <div class="cursor-pointer" wire:click="setField({{ $u->user_id }}, 'patronymic')">
                            {{ $isRow ? $row['patronymic'] : $u->patronymic }}
                        </div>
                    @endif
                </td>

                {{-- ДАТА РОЖДЕНИЯ --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='birth_date')
                        <input type="date" wire:model.defer="row.birth_date"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                        @error('row.birth_date')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                    @else
                        <div class="cursor-pointer" wire:click="setField({{ $u->user_id }}, 'birth_date')">
                            {{ $isRow ? $row['birth_date'] : optional($u->birth_date)->format('Y-m-d') }}
                        </div>
                    @endif
                </td>

                {{-- EMAIL --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='email')
                        <input type="email" wire:model.defer="row.email"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                        @error('row.email')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                    @else
                        <div class="cursor-pointer" wire:click="setField({{ $u->user_id }}, 'email')">
                            {{ $isRow ? $row['email'] : $u->email }}
                        </div>
                    @endif
                </td>

                {{-- ТЕЛЕФОН --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='phone')
                        <input type="text" wire:model.defer="row.phone"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                    @else
                        <div class="cursor-pointer" wire:click="setField({{ $u->user_id }}, 'phone')">
                            {{ $isRow ? $row['phone'] : $u->phone }}
                        </div>
                    @endif
                </td>

                {{-- КАРТА --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='card')
                        <input type="text" wire:model.defer="row.card"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                    @else
                        <div class="cursor-pointer" wire:click="setField({{ $u->user_id }}, 'card')">
                            {{ $isRow ? $row['card'] : $u->card }}
                        </div>
                    @endif
                </td>

                {{-- ПАРОЛЬ (задаём новый) --}}
                <td class="p-2 border">
                    @if($isRow && $editingField==='password')
                        <input type="password" wire:model.defer="passwordNew"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full" placeholder="Новый пароль">
                        @error('passwordNew')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                    @else
                        <div class="cursor-pointer text-blue-600" wire:click="setField({{ $u->user_id }}, 'password')">
                            задать
                        </div>
                    @endif
                </td>

                <td class="p-2 border">
                    @if(!$isRow)
                        <button class="text-indigo-600" wire:click="startEdit({{ $u->user_id }})">✏️</button>
                        <button class="text-red-600 ml-3" wire:click="deleteUser({{ $u->user_id }})"
                                onclick="return confirm('Удалить пользователя?')">🗑️</button>
                    @else
                        <button class="bg-green-600 text-white px-3 py-1 rounded" wire:click="saveRow">Сохранить</button>
                        <button class="ml-2 border px-3 py-1 rounded" wire:click="cancelEdit">Отмена</button>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>

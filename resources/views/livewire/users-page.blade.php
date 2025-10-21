<div class="max-w-[1400px] mx-auto p-6">
    <div class="flex items-center justify-between gap-4 mb-4">
        <input type="text" wire:model.debounce.400ms="search"
               class="border rounded px-3 py-2 w-full"
               placeholder="Поиск (имя, email, телефон)…">
        <button class="bg-indigo-600 text-white rounded px-4 py-2"
                wire:click="create">+ Новый</button>
    </div>

    @if (session('ok'))
        <div class="bg-green-100 text-green-800 px-3 py-2 rounded mb-3">
            {{ session('ok') }}
        </div>
    @endif

    <div class="overflow-x-auto">
    <table class="min-w-full text-sm border">
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
            <th class="p-2 border w-32">Действия</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $u)
            <tr class="border-t hover:bg-gray-50">
                <td class="p-2 border">{{ $u->user_id }}</td>
                <td class="p-2 border">{{ $u->roles->name ?? '—' }}</td>
                <td class="p-2 border">{{ $u->name }}</td>
                <td class="p-2 border">{{ $u->sename }}</td>
                <td class="p-2 border">{{ $u->patronymic }}</td>
                <td class="p-2 border">{{ optional($u->birth_date)->format('Y-m-d') }}</td>
                <td class="p-2 border">{{ $u->email }}</td>
                <td class="p-2 border">{{ $u->phone }}</td>
                <td class="p-2 border">{{ $u->card }}</td>
                <td class="p-2 border text-right">
                    <button class="text-indigo-600" wire:click="edit({{ $u->user_id }})">✏️</button>
                    <button class="text-red-600 ml-3" wire:click="delete({{ $u->user_id }})"
                            onclick="return confirm('Удалить пользователя?')">🗑️</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>

    {{-- Модалка формы (Alpine.js подключи в layout) --}}
    <div x-data="{open:false}" x-show="open" x-cloak
         @open-form.window="open=true" @close-form.window="open=false"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
            <h2 class="text-lg font-semibold mb-4">
                {{ $editingId ? 'Редактировать пользователя' : 'Новый пользователь' }}
            </h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Роль</label>
                    <select wire:model.defer="role_id" class="border rounded w-full px-2 py-1">
                        <option value="">— выбери —</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->role_id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1">Имя</label>
                    <input type="text" wire:model.defer="name" class="border rounded w-full px-2 py-1">
                    @error('name')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1">Фамилия</label>
                    <input type="text" wire:model.defer="sename" class="border rounded w-full px-2 py-1">
                </div>

                <div>
                    <label class="block text-sm mb-1">Отчество</label>
                    <input type="text" wire:model.defer="patronymic" class="border rounded w-full px-2 py-1">
                </div>

                <div>
                    <label class="block text-sm mb-1">Дата рождения</label>
                    <input type="date" wire:model.defer="birth_date" class="border rounded w-full px-2 py-1">
                    @error('birth_date')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" wire:model.defer="email" class="border rounded w-full px-2 py-1">
                    @error('email')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm mb-1">Телефон</label>
                    <input type="text" wire:model.defer="phone" class="border rounded w-full px-2 py-1">
                </div>

                <div>
                    <label class="block text-sm mb-1">Карта</label>
                    <input type="text" wire:model.defer="card" class="border rounded w-full px-2 py-1">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm mb-1">Пароль
                        <span class="text-gray-500 text-xs">(при редактировании оставь пустым, если не меняешь)</span>
                    </label>
                    <input type="password" wire:model.defer="password" class="border rounded w-full px-2 py-1">
                    @error('password')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button class="px-4 py-2 rounded border"
                        @click="open=false; window.dispatchEvent(new CustomEvent('close-form'))">Отмена</button>
                <button class="px-4 py-2 rounded bg-indigo-600 text-white" wire:click="save">Сохранить</button>
            </div>
        </div>
    </div>
</div>

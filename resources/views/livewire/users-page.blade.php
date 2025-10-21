<div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between mb-4 items-center">
        <input type="text" wire:model.debounce.500ms="search"
               placeholder="Поиск по имени или email"
               class="border rounded px-3 py-2 w-1/2">
        <button wire:click="create"
                class="bg-indigo-600 text-white rounded px-4 py-2">
            + Новый пользователь
        </button>
    </div>

    @if (session('ok'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-3">
            {{ session('ok') }}
        </div>
    @endif

    <table class="w-full border-collapse border">
        <thead class="bg-gray-100">
        <tr>
            <th class="border p-2">ID</th>
            <th class="border p-2">Имя</th>
            <th class="border p-2">Email</th>
            <th class="border p-2">Телефон</th>
            <th class="border p-2">Роль</th>
            <th class="border p-2"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td class="border p-2">{{ $u->user_id }}</td>
                <td class="border p-2">{{ $u->name }} {{ $u->sename }}</td>
                <td class="border p-2">{{ $u->email }}</td>
                <td class="border p-2">{{ $u->phone }}</td>
                <td class="border p-2">{{ $u->roles?->name ?? '—' }}</td>
                <td class="border p-2 text-right">
                    <button wire:click="edit({{ $u->user_id }})" class="text-blue-600">Редактировать</button>
                    <button wire:click="delete({{ $u->user_id }})"
                            onclick="return confirm('Удалить пользователя?')"
                            class="text-red-600 ml-2">Удалить</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-3">{{ $users->links() }}</div>

    {{-- Модальное окно для формы --}}
    <div x-data="{open:false}" x-show="open" x-cloak
         @open-form.window="open=true" @close-form.window="open=false"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h2 class="text-lg font-semibold mb-4">
                {{ $editingId ? 'Редактировать пользователя' : 'Новый пользователь' }}
            </h2>

            <div class="grid gap-3">
                <div>
                    <label>Имя</label>
                    <input type="text" wire:model.defer="name" class="w-full border rounded px-2 py-1">
                    @error('name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label>Фамилия</label>
                    <input type="text" wire:model.defer="sename" class="w-full border rounded px-2 py-1">
                </div>

                <div>
                    <label>Отчество</label>
                    <input type="text" wire:model.defer="patronymic" class="w-full border rounded px-2 py-1">
                </div>

                <div>
                    <label>Email</label>
                    <input type="email" wire:model.defer="email" class="w-full border rounded px-2 py-1">
                    @error('email') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label>Пароль</label>
                    <input type="password" wire:model.defer="password" class="w-full border rounded px-2 py-1">
                </div>

                <div>
                    <label>Телефон</label>
                    <input type="text" wire:model.defer="phone" class="w-full border rounded px-2 py-1">
                </div>

                <div>
                    <label>Карта</label>
                    <input type="text" wire:model.defer="card" class="w-full border rounded px-2 py-1">
                </div>

                <div>
                    <label>Роль</label>
                    <select wire:model.defer="role_id" class="w-full border rounded px-2 py-1">
                        <option value="">— выбери —</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id_role }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button class="border rounded px-3 py-1"
                        @click="open=false; window.dispatchEvent(new CustomEvent('close-form'))">Отмена</button>
                <button wire:click="save"
                        class="bg-indigo-600 text-white rounded px-4 py-1">Сохранить</button>
            </div>
        </div>
    </div>
</div>

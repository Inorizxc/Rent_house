@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- Пользователь --}}
    <div>
        <label class="block text-sm font-medium mb-1">Пользователь</label>
        <select name="user_id" class="border rounded w-full px-3 py-2">
            <option value="">— Не выбран —</option>
            @foreach ($users as $user)
                <option value="{{ $user->user_id }}"
                    {{ old('user_id', $house->user_id ?? '') == $user->user_id ? 'selected' : '' }}>
                    {{ trim(($user->sename ?? '').' '.($user->name ?? '').' '.($user->patronymic ?? '')) ?: 'User #'.$user->user_id }}
                </option>
            @endforeach
        </select>
        @error('user_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Адрес --}}
    <div>
        <label class="block text-sm font-medium mb-1">Адрес *</label>
        <input type="text" name="adress" value="{{ old('adress', $house->adress ?? '') }}"
               class="border rounded w-full px-3 py-2">
        @error('adress') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Площадь --}}
    <div>
        <label class="block text-sm font-medium mb-1">Площадь</label>
        <input type="number" step="0.01" name="area" value="{{ old('area', $house->area ?? '') }}"
               class="border rounded w-full px-3 py-2">
        @error('area') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Цена --}}
    <div>
        <label class="block text-sm font-medium mb-1">Цена</label>
        <input type="number" name="price_id" value="{{ old('price_id', $house->price_id ?? '') }}"
               class="border rounded w-full px-3 py-2">
        @error('price_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Тип аренды --}}
    <div>
        <label class="block text-sm font-medium mb-1">Тип аренды</label>
        <input type="number" name="rent_type_id" value="{{ old('rent_type_id', $house->rent_type_id ?? '') }}"
               class="border rounded w-full px-3 py-2">
        @error('rent_type_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Тип дома --}}
    <div>
        <label class="block text-sm font-medium mb-1">Тип дома</label>
        <input type="number" name="house_type_id" value="{{ old('house_type_id', $house->house_type_id ?? '') }}"
               class="border rounded w-full px-3 py-2">
        @error('house_type_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Координаты --}}
    <div>
        <label class="block text-sm font-medium mb-1">Широта</label>
        <input type="number" step="0.0000001" name="lat" value="{{ old('lat', $house->lat ?? '') }}"
               class="border rounded w-full px-3 py-2">
        @error('lat') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Долгота</label>
        <input type="number" step="0.0000001" name="lng" value="{{ old('lng', $house->lng ?? '') }}"
               class="border rounded w-full px-3 py-2">
        @error('lng') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Фото --}}
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Фото</label>
        <input type="file" name="image" class="block w-full text-sm">
        @error('image') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Удалён --}}
    <div>
        <label class="block text-sm font-medium mb-1">Удалён?</label>
        <select name="is_deleted" class="border rounded w-full px-3 py-2">
            <option value="0" {{ old('is_deleted', $house->is_deleted ?? 0) == 0 ? 'selected' : '' }}>Нет</option>
            <option value="1" {{ old('is_deleted', $house->is_deleted ?? 0) == 1 ? 'selected' : '' }}>Да</option>
        </select>
    </div>
</div>

<div class="mt-6 flex justify-end">
    <button type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Сохранить
    </button>
</div>

@csrf
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Пользователь</label>
        <select name="user_id" class="w-full border rounded px-3 py-2">
            <option value="">— Не выбран —</option>
            @foreach($users as $u)
                <option value="{{ $u->user_id }}" @selected(old('user_id', $house->user_id) == $u->user_id)>
                    {{ trim(($u->sename ?? '').' '.($u->name ?? '').' '.($u->patronymic ?? '')) ?: ('User #'.$u->user_id) }}
                </option>
            @endforeach
        </select>
        @error('user_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Адрес *</label>
        <input type="text" name="adress" value="{{ old('adress', $house->adress) }}" class="w-full border rounded px-3 py-2">
        @error('adress') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Площадь</label>
        <input type="number" step="0.01" name="area" value="{{ old('area', $house->area) }}" class="w-full border rounded px-3 py-2">
        @error('area') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Цена ID</label>
        <input type="number" name="price_id" value="{{ old('price_id', $house->price_id) }}" class="w-full border rounded px-3 py-2">
        @error('price_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Тип аренды ID</label>
        <input type="number" name="rent_type_id" value="{{ old('rent_type_id', $house->rent_type_id) }}" class="w-full border rounded px-3 py-2">
        @error('rent_type_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Тип дома ID</label>
        <input type="number" name="house_type_id" value="{{ old('house_type_id', $house->house_type_id) }}" class="w-full border rounded px-3 py-2">
        @error('house_type_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Календарь ID</label>
        <input type="number" name="calendar_id" value="{{ old('calendar_id', $house->calendar_id) }}" class="w-full border rounded px-3 py-2">
        @error('calendar_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Широта</label>
        <input type="number" step="0.0000001" name="lat" value="{{ old('lat', $house->lat) }}" class="w-full border rounded px-3 py-2">
        @error('lat') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Долгота</label>
        <input type="number" step="0.0000001" name="lng" value="{{ old('lng', $house->lng) }}" class="w-full border rounded px-3 py-2">
        @error('lng') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm mb-1">Пометка удалён</label>
        <select name="is_deleted" class="w-full border rounded px-3 py-2">
            <option value="0" @selected(old('is_deleted', (int)$house->is_deleted) === 0)>Нет</option>
            <option value="1" @selected(old('is_deleted', (int)$house->is_deleted) === 1)>Да</option>
        </select>
        @error('is_deleted') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm mb-1">Фото</label>
        <input type="file" name="image" accept="image/*" class="block w-full text-sm">
        @error('image') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

        @isset($house->house_id)
            <div class="mt-2">
                <img src="{{ $house->image_url }}" alt="preview" class="max-h-40 rounded border">
            </div>
        @endisset
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-2">
    <a href="{{ route('houses.index') }}" class="px-4 py-2 border rounded">Отмена</a>
    <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Сохранить</button>
</div>

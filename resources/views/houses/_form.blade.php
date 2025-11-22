@csrf

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
    {{-- Пользователь (Арендодатель) --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Арендодатель</label>
        @if($currentUser && $currentUser->isAdmin())
            {{-- Для администраторов: выбор из списка --}}
            <select name="user_id" style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
                <option value="">— Не выбран —</option>
                @foreach ($users as $user)
                    @php
                        $fullName = trim(($user->sename ?? '').' '.($user->name ?? '').' '.($user->patronymic ?? '')) ?: 'User #'.$user->user_id;
                    @endphp
                    <option value="{{ $user->user_id }}"
                        {{ old('user_id', $house->user_id ?? '') == $user->user_id ? 'selected' : '' }}>
                        {{ $fullName }}
                    </option>
                @endforeach
            </select>
        @elseif($currentUser)
            {{-- Для арендодателей: только их имя, без возможности изменить --}}
            @php
                $currentUserFullName = trim(($currentUser->sename ?? '').' '.($currentUser->name ?? '').' '.($currentUser->patronymic ?? '')) ?: 'User #'.$currentUser->user_id;
            @endphp
            <input type="hidden" name="user_id" value="{{ $currentUser->user_id }}">
            <input type="text" value="{{ $currentUserFullName }}" disabled 
                   style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #f5f5f5; color: #666; cursor: not-allowed;">
        @endif
        @error('user_id') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Адрес --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Адрес <span style="color: #dc2626;">*</span></label>
        <input type="text" name="adress" value="{{ old('adress', $house->adress ?? '') }}"
               style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
        @error('adress') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Площадь --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Площадь (м²)</label>
        <input type="number" step="0.01" name="area" value="{{ old('area', $house->area ?? '') }}"
               style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
        @error('area') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Цена --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Цена</label>
        <input type="number" name="price_id" value="{{ old('price_id', $house->price_id ?? '') }}"
               style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
        @error('price_id') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Тип аренды --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Тип аренды</label>
        <select name="rent_type_name" style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
            <option value="">— Не выбран —</option>
            @foreach ($rentTypes as $rentType)
                @php
                    $currentRentTypeName = old('rent_type_name', isset($house->rent_type) && $house->rent_type ? $house->rent_type->name : '');
                    $selected = $currentRentTypeName == $rentType->name;
                @endphp
                <option value="{{ $rentType->name }}" {{ $selected ? 'selected' : '' }}>
                    {{ $rentType->name }}
                </option>
            @endforeach
        </select>
        @error('rent_type_name') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Тип дома --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Тип дома</label>
        <select name="house_type_name" style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
            <option value="">— Не выбран —</option>
            @foreach ($houseTypes as $houseType)
                @php
                    $currentHouseTypeName = old('house_type_name', isset($house->house_type) && $house->house_type ? $house->house_type->name : '');
                    $selected = $currentHouseTypeName == $houseType->name;
                @endphp
                <option value="{{ $houseType->name }}" {{ $selected ? 'selected' : '' }}>
                    {{ $houseType->name }}
                </option>
            @endforeach
        </select>
        @error('house_type_name') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Широта --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Широта</label>
        <input type="number" step="0.0000001" name="lat" value="{{ old('lat', $house->lat ?? '') }}"
               style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
        @error('lat') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Долгота --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Долгота</label>
        <input type="number" step="0.0000001" name="lng" value="{{ old('lng', $house->lng ?? '') }}"
               style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
        @error('lng') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Удалён --}}
    <div>
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Удалён?</label>
        <select name="is_deleted" style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
            <option value="0" {{ old('is_deleted', $house->is_deleted ?? 0) == 0 ? 'selected' : '' }}>Нет</option>
            <option value="1" {{ old('is_deleted', $house->is_deleted ?? 0) == 1 ? 'selected' : '' }}>Да</option>
        </select>
        @error('is_deleted') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>

    {{-- Фото --}}
    <div style="grid-column: 1 / -1;">
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #333;">Фото</label>
        <input type="file" name="image" accept="image/*" 
               style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
        @error('image') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
    </div>
</div>

<div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px;">
    <a href="{{ route('houses.index') }}" 
       style="padding: 10px 20px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fff; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; transition: 0.2s ease; display: inline-block;"
       onmouseover="this.style.background='#f5f5f5'; this.style.borderColor='#d0d0d0';"
       onmouseout="this.style.background='#fff'; this.style.borderColor='#e0e0e0';">
        Отмена
    </a>
    <button type="submit"
            style="padding: 10px 20px; border: none; border-radius: 8px; background: #2563eb; color: #fff; font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s ease;"
            onmouseover="this.style.background='#1d4ed8';"
            onmouseout="this.style.background='#2563eb';">
        Сохранить
    </button>
</div>

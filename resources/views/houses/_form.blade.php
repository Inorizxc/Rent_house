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
        <div style="position: relative;">
            <input type="text" name="adress" id="address-input" value="{{ old('adress', $house->adress ?? '') }}"
                   placeholder="Например: Саратов, улица Исаева, 5"
                   style="width: 100%; padding: 10px 14px; padding-right: 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
            <span id="address-loading" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #2563eb; font-size: 16px;">⏳</span>
            <span id="address-success" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #10b981; font-size: 16px;">✓</span>
            <span id="address-error" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #dc2626; font-size: 16px;">✗</span>
        </div>
        <p id="address-status" style="font-size: 11px; color: #666; margin-top: 4px; font-style: italic; min-height: 16px;">
            Формат: Город, улица, номер дома. Например: "Саратов, улица Исаева, 5" или "ул. Исаева, 5"
        </p>
        @if(Route::is('houses.create'))
        <div id="request-url-container" style="margin-top: 8px; padding: 10px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; display: none;">
            <p style="font-size: 11px; font-weight: 600; color: #333; margin-bottom: 6px;">Сформированная ссылка для запроса:</p>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <div>
                    <p style="font-size: 10px; color: #666; margin-bottom: 2px;">Запрос к нашему серверу:</p>
                    <code id="server-request-url" style="font-size: 10px; color: #2563eb; word-break: break-all; display: block; padding: 4px; background: #fff; border-radius: 4px;"></code>
                </div>
                <div>
                    <p style="font-size: 10px; color: #666; margin-bottom: 2px;">Примерный запрос к Yandex API:</p>
                    <code id="yandex-request-url" style="font-size: 10px; color: #10b981; word-break: break-all; display: block; padding: 4px; background: #fff; border-radius: 4px;"></code>
                </div>
            </div>
        </div>
        @endif
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

    {{-- Координаты теперь заполняются автоматически через геокодер --}}
    {{-- Скрытые поля для сохранения координат --}}
    <input type="hidden" name="lat" id="lat-input" value="{{ old('lat', $house->lat ?? '') }}">
    <input type="hidden" name="lng" id="lng-input" value="{{ old('lng', $house->lng ?? '') }}">

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
        
        {{-- Существующие фотографии (только для редактирования) --}}
        @if(isset($house) && $house->house_id && $house->photo && $house->photo->count() > 0)
        <div id="existing-photos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px;">
            @foreach($house->photo as $photo)
            <div class="photo-item" data-photo-id="{{ $photo->photo_id }}" style="position: relative; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #f5f5f5;">
                <img src="{{ asset('storage/' . $photo->path) }}" alt="Фото" 
                     style="width: 100%; height: 150px; object-fit: cover; display: block;">
                <button type="button" class="delete-photo-btn" data-photo-id="{{ $photo->photo_id }}" 
                        style="position: absolute; top: 4px; right: 4px; width: 28px; height: 28px; border: none; border-radius: 50%; background: rgba(220, 38, 38, 0.9); color: #fff; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: 0.2s ease;"
                        onmouseover="this.style.background='rgba(220, 38, 38, 1)';"
                        onmouseout="this.style.background='rgba(220, 38, 38, 0.9)';"
                        title="Удалить фото">
                    ×
                </button>
            </div>
            @endforeach
        </div>
        @endif
        
        {{-- Поле для загрузки новых фотографий --}}
        <input type="file" name="images[]" id="images-input" accept="image/*" multiple
               style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #333;">
        <p style="font-size: 12px; color: #666; margin-top: 4px;">Можно выбрать несколько фотографий одновременно</p>
        
        {{-- Предпросмотр выбранных файлов --}}
        <div id="preview-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-top: 16px; display: none;">
        </div>
        
        {{-- Скрытое поле для хранения ID удаленных фотографий --}}
        <input type="hidden" name="deleted_photos" id="deleted-photos-input" value="">
        
        @error('images.*') 
            <p style="font-size: 12px; color: #dc2626; margin-top: 4px;">{{ $message }}</p> 
        @enderror
        @error('images') 
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

<script>
(function() {
    const addressInput = document.getElementById('address-input');
    const latInput = document.getElementById('lat-input');
    const lngInput = document.getElementById('lng-input');
    const loadingIcon = document.getElementById('address-loading');
    const successIcon = document.getElementById('address-success');
    const errorIcon = document.getElementById('address-error');
    const statusText = document.getElementById('address-status');
    const requestUrlContainer = document.getElementById('request-url-container');
    const serverRequestUrl = document.getElementById('server-request-url');
    const yandexRequestUrl = document.getElementById('yandex-request-url');
    
    let debounceTimer = null;
    let currentRequest = null;
    
    if (!addressInput) return;
    
    // Упрощенная функция нормализации адреса (для отображения примерного URL)
    function normalizeAddressForDisplay(address) {
        if (!address) return '';
        
        // Убираем лишние пробелы
        let normalized = address.replace(/\s+/g, ' ').trim();
        
        // Заменяем запятые на пробелы
        normalized = normalized.replace(/,/g, ' ');
        
        // Убираем служебные слова (упрощенная версия)
        normalized = normalized.replace(/\b(?:ул\.?|улица|проспект|пр\.?|пр-т|переулок|пер\.?|бульвар|бул\.?|дом|д\.?)\s*/gi, '');
        
        // Убираем все знаки препинания, оставляем только буквы и цифры
        normalized = normalized.replace(/[^\p{L}\p{N}]/gu, '');
        
        // Добавляем "Саратов" если нужно
        if (!/^[Сс]аратов/i.test(normalized) && /\d/.test(normalized) && normalized.length < 50) {
            normalized = 'Саратов' + normalized;
        }
        
        return normalized.trim();
    }
    
    // Функция для обновления отображения URL запросов
    function updateRequestUrls(address) {
        if (!requestUrlContainer || !address || address.trim().length < 5) {
            if (requestUrlContainer) {
                requestUrlContainer.style.display = 'none';
            }
            return;
        }
        
        // URL запроса к нашему серверу
        const serverUrl = '{{ route("houses.get-coordinates") }}';
        const serverFullUrl = window.location.origin + serverUrl;
        if (serverRequestUrl) {
            serverRequestUrl.textContent = `POST ${serverFullUrl}`;
        }
        
        // Примерный URL запроса к Yandex API
        const normalizedAddress = normalizeAddressForDisplay(address);
        const apiKey = 'a2cd05de-c1e4-457b-8092-a8b0ebd9db10';
        const yandexUrl = `https://geocode-maps.yandex.ru/v1/?apikey=${apiKey}&geocode=${encodeURIComponent(normalizedAddress)}&format=json`;
        if (yandexRequestUrl) {
            yandexRequestUrl.textContent = yandexUrl;
        }
        
        if (requestUrlContainer) {
            requestUrlContainer.style.display = 'block';
        }
    }
    
    // Функция для сброса всех индикаторов
    function resetIndicators() {
        loadingIcon.style.display = 'none';
        successIcon.style.display = 'none';
        errorIcon.style.display = 'none';
    }
    
    // Функция для получения координат
    function fetchCoordinates(address) {
        // Отменяем предыдущий запрос, если он еще выполняется
        if (currentRequest) {
            currentRequest.abort();
        }
        
        if (!address || address.trim().length < 5) {
            resetIndicators();
            statusText.textContent = 'Введите адрес для автоматического определения координат';
            statusText.style.color = '#666';
            latInput.value = '';
            lngInput.value = '';
            if (requestUrlContainer) {
                requestUrlContainer.style.display = 'none';
            }
            return;
        }
        
        // Обновляем отображение URL запросов
        updateRequestUrls(address);
        
        // Показываем индикатор загрузки
        resetIndicators();
        loadingIcon.style.display = 'block';
        statusText.textContent = 'Определение координат...';
        statusText.style.color = '#2563eb';
        
        // Создаем новый запрос
        const xhr = new XMLHttpRequest();
        currentRequest = xhr;
        
        const serverUrl = '{{ route("houses.get-coordinates") }}';
        xhr.open('POST', serverUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            currentRequest = null;
            resetIndicators();
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success && response.lat && response.lng) {
                        latInput.value = response.lat;
                        lngInput.value = response.lng;
                        successIcon.style.display = 'block';
                        statusText.textContent = `Координаты получены: ${parseFloat(response.lat).toFixed(6)}, ${parseFloat(response.lng).toFixed(6)}`;
                        statusText.style.color = '#10b981';
                    } else {
                        errorIcon.style.display = 'block';
                        statusText.textContent = response.message || 'Не удалось получить координаты';
                        statusText.style.color = '#dc2626';
                        latInput.value = '';
                        lngInput.value = '';
                    }
                } catch (e) {
                    errorIcon.style.display = 'block';
                    statusText.textContent = 'Ошибка при обработке ответа';
                    statusText.style.color = '#dc2626';
                    latInput.value = '';
                    lngInput.value = '';
                }
            } else {
                errorIcon.style.display = 'block';
                try {
                    const response = JSON.parse(xhr.responseText);
                    statusText.textContent = response.message || 'Ошибка при получении координат';
                } catch (e) {
                    statusText.textContent = 'Ошибка при получении координат. Проверьте подключение к интернету.';
                }
                statusText.style.color = '#dc2626';
                latInput.value = '';
                lngInput.value = '';
            }
        };
        
        xhr.onerror = function() {
            currentRequest = null;
            resetIndicators();
            errorIcon.style.display = 'block';
            statusText.textContent = 'Ошибка сети. Проверьте подключение к интернету.';
            statusText.style.color = '#dc2626';
            latInput.value = '';
            lngInput.value = '';
        };
        
        xhr.onabort = function() {
            currentRequest = null;
        };
        
        xhr.send(JSON.stringify({ address: address.trim() }));
    }
    
    // Обработчик ввода с debounce (задержка 800мс после последнего ввода)
    addressInput.addEventListener('input', function() {
        const address = this.value;
        
        // Обновляем URL запросов сразу при вводе (без задержки)
        updateRequestUrls(address);
        
        // Очищаем предыдущий таймер
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        
        // Устанавливаем новый таймер
        debounceTimer = setTimeout(function() {
            fetchCoordinates(address);
        }, 800);
    });
    
    // Если адрес уже заполнен при загрузке страницы, получаем координаты
    if (addressInput.value && addressInput.value.trim().length >= 5) {
        // Небольшая задержка, чтобы страница успела загрузиться
        setTimeout(function() {
            fetchCoordinates(addressInput.value);
        }, 500);
    }
})();

// Обработка фотографий
(function() {
    const imagesInput = document.getElementById('images-input');
    const previewContainer = document.getElementById('preview-container');
    const deletedPhotosInput = document.getElementById('deleted-photos-input');
    let deletedPhotos = [];
    
    // Загружаем список удаленных фотографий из скрытого поля
    if (deletedPhotosInput && deletedPhotosInput.value) {
        try {
            deletedPhotos = JSON.parse(deletedPhotosInput.value);
        } catch(e) {
            deletedPhotos = [];
        }
    }
    
    // Предпросмотр выбранных файлов
    if (imagesInput) {
        imagesInput.addEventListener('change', function(e) {
            previewContainer.innerHTML = '';
            previewContainer.style.display = 'none';
            
            const files = Array.from(e.target.files);
            if (files.length === 0) return;
            
            previewContainer.style.display = 'grid';
            
            files.forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.style.cssText = 'position: relative; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #f5f5f5;';
                    div.dataset.fileIndex = index;
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width: 100%; height: 150px; object-fit: cover; display: block;';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-preview-btn';
                    removeBtn.textContent = '×';
                    removeBtn.style.cssText = 'position: absolute; top: 4px; right: 4px; width: 28px; height: 28px; border: none; border-radius: 50%; background: rgba(220, 38, 38, 0.9); color: #fff; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: 0.2s ease;';
                    removeBtn.onmouseover = function() { this.style.background = 'rgba(220, 38, 38, 1)'; };
                    removeBtn.onmouseout = function() { this.style.background = 'rgba(220, 38, 38, 0.9)'; };
                    removeBtn.onclick = function() {
                        // Находим индекс элемента в контейнере
                        const previewItems = Array.from(previewContainer.children);
                        const itemIndex = previewItems.indexOf(div);
                        
                        // Удаляем файл из input
                        const dt = new DataTransfer();
                        const filesArray = Array.from(imagesInput.files);
                        if (itemIndex >= 0 && itemIndex < filesArray.length) {
                            filesArray.splice(itemIndex, 1);
                            filesArray.forEach(f => dt.items.add(f));
                            imagesInput.files = dt.files;
                        }
                        
                        // Удаляем превью
                        div.remove();
                        
                        // Если превью пусто, скрываем контейнер
                        if (previewContainer.children.length === 0) {
                            previewContainer.style.display = 'none';
                        }
                    };
                    
                    div.appendChild(img);
                    div.appendChild(removeBtn);
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    }
    
    // Удаление существующих фотографий
    document.querySelectorAll('.delete-photo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const photoId = this.dataset.photoId;
            const photoItem = this.closest('.photo-item');
            
            if (confirm('Удалить это фото?')) {
                // Добавляем ID в список удаленных
                if (!deletedPhotos.includes(photoId)) {
                    deletedPhotos.push(photoId);
                    deletedPhotosInput.value = JSON.stringify(deletedPhotos);
                }
                
                // Скрываем элемент (удаление произойдет на сервере)
                photoItem.style.opacity = '0.5';
                photoItem.style.pointerEvents = 'none';
                this.disabled = true;
            }
        });
    });
})();
</script>

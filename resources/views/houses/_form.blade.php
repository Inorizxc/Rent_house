@csrf

<div class="house-form-grid">
    <div class="house-form-field">
        <label class="house-form-label">Арендодатель</label>
        @if($currentUser && $currentUser->isAdmin())
            <select name="user_id" class="house-form-select">
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
            @php
                $currentUserFullName = trim(($currentUser->sename ?? '').' '.($currentUser->name ?? '').' '.($currentUser->patronymic ?? '')) ?: 'User #'.$currentUser->user_id;
            @endphp
            <input type="hidden" name="user_id" value="{{ $currentUser->user_id }}">
            <input type="text" value="{{ $currentUserFullName }}" disabled class="house-form-input">
        @endif
        @error('user_id') 
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
    </div>

    <div class="house-form-field">
        <label class="house-form-label">Адрес <span class="required">*</span></label>
        <div class="house-form-input-wrapper">
            <input type="text" name="adress" id="address-input" value="{{ old('adress', $house->adress ?? '') }}"
                   placeholder="Начните вводить адрес..."
                   autocomplete="off"
                   class="house-form-input" style="padding-right: 40px;">
            <span id="address-loading" class="house-form-input-icon loading">⏳</span>
            <span id="address-success" class="house-form-input-icon success">✓</span>
            <span id="address-error" class="house-form-input-icon error">✗</span>
            
            <div id="address-suggestions" class="house-form-suggestions">
            </div>
        </div>
        <p id="address-status" class="house-form-status">
            Формат: Город, ул. Название улицы, д Номер. Например: "Саратов, ул. Степана Разина, д 93"
        </p>
        @if(Route::is('houses.create'))
        <div id="request-url-container" class="house-form-request-url">
            <p class="house-form-request-url-title">Сформированная ссылка для запроса:</p>
            <div class="house-form-request-url-content">
                <div class="house-form-request-url-item">
                    <p class="house-form-request-url-label">Запрос к нашему серверу:</p>
                    <code id="server-request-url" class="house-form-request-url-code server"></code>
                </div>
                <div class="house-form-request-url-item">
                    <p class="house-form-request-url-label">Примерный запрос к Yandex API:</p>
                    <code id="yandex-request-url" class="house-form-request-url-code yandex"></code>
                </div>
            </div>
        </div>
        @endif
        @error('adress') 
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
    </div>

    <div class="house-form-field">
        <label class="house-form-label">Площадь (м²)</label>
        <input type="number" step="0.01" name="area" value="{{ old('area', $house->area ?? '') }}" class="house-form-input">
        @error('area') 
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
    </div>

    <div class="house-form-field">
        <label class="house-form-label">Цена</label>
        <input type="number" name="price_id" value="{{ old('price_id', $house->price_id ?? '') }}" class="house-form-input">
        @error('price_id') 
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
    </div>

    <div class="house-form-field">
        <label class="house-form-label">Тип аренды</label>
        <select name="rent_type_name" class="house-form-select">
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
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
    </div>

    <div class="house-form-field">
        <label class="house-form-label">Тип дома</label>
        <select name="house_type_name" class="house-form-select">
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
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
    </div>

    <input type="hidden" name="lat" id="lat-input" value="{{ old('lat', $house->lat ?? '') }}">
    <input type="hidden" name="lng" id="lng-input" value="{{ old('lng', $house->lng ?? '') }}">

    <div class="house-form-field">
        <label class="house-form-label">Удалён?</label>
        <select name="is_deleted" class="house-form-select">
            <option value="0" {{ old('is_deleted', $house->is_deleted ?? 0) == 0 ? 'selected' : '' }}>Нет</option>
            <option value="1" {{ old('is_deleted', $house->is_deleted ?? 0) == 1 ? 'selected' : '' }}>Да</option>
        </select>
        @error('is_deleted') 
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
    </div>

    <div class="house-form-photos">
        <label class="house-form-label">Фото</label>
        
        @if(isset($house) && $house->house_id && $house->photo && $house->photo->count() > 0)
        <div id="existing-photos" class="house-form-photos-grid">
            @foreach($house->photo as $photo)
            <div class="house-form-photo-item" data-photo-id="{{ $photo->photo_id }}">
                <img src="{{ asset('storage/' . $photo->path) }}" alt="Фото" class="house-form-photo-img">
                <button type="button" class="house-form-photo-delete delete-photo-btn" data-photo-id="{{ $photo->photo_id }}" title="Удалить фото">
                    ×
                </button>
            </div>
            @endforeach
        </div>
        @endif
        
        <input type="file" name="images[]" id="images-input" accept="image/*" multiple class="house-form-input">
        <p class="house-form-status">Можно выбрать несколько фотографий одновременно</p>
        
        <div id="preview-container" class="house-form-photo-preview">
        </div>
        
        <input type="hidden" name="deleted_photos" id="deleted-photos-input" value="">
        
        @error('images.*') 
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
        @error('images') 
            <p class="house-form-error">{{ $message }}</p> 
        @enderror
    </div>
</div>

<div class="house-form-actions">
    <a href="{{ route('map') }}" class="house-form-btn-cancel">
        Отмена
    </a>
    <button type="submit" class="house-form-btn-submit">
        Сохранить
    </button>
</div>

<script>

// Автокомплит адреса
(function() {
    const addressInput = document.getElementById('address-input');
    const suggestionsContainer = document.getElementById('address-suggestions');
    let suggestionsDebounceTimer = null;
    let currentSuggestionsRequest = null;
    let selectedSuggestionIndex = -1;
    let suggestions = [];
    
    if (!addressInput || !suggestionsContainer) return;
    
    function fetchSuggestions(query) {
        if (!query || query.trim().length < 2) {
            hideSuggestions();
            return;
        }
        
        const trimmedQuery = query.trim();
        console.log('Fetching suggestions for:', trimmedQuery);
        
        if (currentSuggestionsRequest) {
            currentSuggestionsRequest.abort();
        }
        
        const xhr = new XMLHttpRequest();
        currentSuggestionsRequest = xhr;
        
        const serverUrl = '{{ route("houses.get-address-suggestions") }}';
        xhr.open('POST', serverUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            currentSuggestionsRequest = null;
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log('Suggestions response:', response);
                    
                    if (response.success && Array.isArray(response.suggestions)) {
                        suggestions = response.suggestions;
                        console.log('Found suggestions:', suggestions.length, suggestions);
                        if (suggestions.length > 0) {
                            displaySuggestions(suggestions);
                        } else {
                            console.log('No suggestions to display');
                            hideSuggestions();
                        }
                    } else {
                        console.warn('No suggestions in response or invalid format:', response);
                        hideSuggestions();
                    }
                } catch (e) {
                    console.error('Error parsing suggestions response:', e, xhr.responseText);
                }
            } else {
                console.error('Suggestions request failed with status:', xhr.status, xhr.responseText);
                hideSuggestions();
            }
        };
        
        xhr.onerror = function() {
            console.error('Network error while fetching suggestions')
            currentSuggestionsRequest = null;
            hideSuggestions();
        };
        
        xhr.onabort = function() {
            console.log('Suggestions request aborted');
            currentSuggestionsRequest = null;
        };
        
        xhr.send(JSON.stringify({ query: trimmedQuery }));
    }
    
    function displaySuggestions(suggestionsList) {
        if (!suggestionsList || suggestionsList.length === 0) {
            hideSuggestions();
            return;
        }
        
        suggestionsContainer.innerHTML = '';
        
        suggestionsList.forEach((suggestion, index) => {
            const item = document.createElement('div');
            item.className = 'house-form-suggestion-item';
            item.dataset.index = index;
            item.textContent = suggestion.display || suggestion.value || '';
            

            item.onmouseenter = function() {
                this.style.backgroundColor = '#e3f2fd';
                selectedSuggestionIndex = index;
            };
            item.onmouseleave = function() {
                if (selectedSuggestionIndex !== index) {
                    this.style.backgroundColor = '#fff';
                }
            };
            
            item.onclick = function() {
                selectSuggestion(suggestion);
            };
            
            suggestionsContainer.appendChild(item);
        });
        
        const lastItem = suggestionsContainer.lastElementChild;
        if (lastItem) {
            lastItem.style.borderBottom = 'none';
        }
        
        suggestionsContainer.style.display = 'block';
        selectedSuggestionIndex = -1;
    }
    
    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
        suggestionsContainer.innerHTML = '';
        suggestions = [];
        selectedSuggestionIndex = -1;
    }
    
    function selectSuggestion(suggestion) {
        const address = suggestion.value || suggestion.display || '';
        addressInput.value = address;
        hideSuggestions();
        
        addressInput.dispatchEvent(new Event('input'));
        
        addressInput.focus();
    }
    
    addressInput.addEventListener('input', function() {
        const query = this.value;
        
        if (suggestionsDebounceTimer) {
            clearTimeout(suggestionsDebounceTimer);
        }
        
        if (!query || query.trim().length < 2) {
            hideSuggestions();
            return;
        }
        
        suggestionsDebounceTimer = setTimeout(function() {
            fetchSuggestions(query);
        }, 300);
    });
    
    addressInput.addEventListener('focus', function() {
        const query = this.value;
        if (query && query.trim().length >= 2 && suggestions.length > 0) {
            displaySuggestions(suggestions);
        }
    });
    
    addressInput.addEventListener('blur', function() {
        setTimeout(function() {
            hideSuggestions();
        }, 200);
    });
    
    addressInput.addEventListener('keydown', function(e) {
        if (suggestionsContainer.style.display === 'none' || suggestions.length === 0) {
            return;
        }
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1, suggestions.length - 1);
            highlightSuggestion(selectedSuggestionIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
            if (selectedSuggestionIndex >= 0) {
                highlightSuggestion(selectedSuggestionIndex);
            } else {
                clearHighlight();
            }
        } else if (e.key === 'Enter' && selectedSuggestionIndex >= 0) {
            e.preventDefault();
            selectSuggestion(suggestions[selectedSuggestionIndex]);
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });
    
    function highlightSuggestion(index) {
        const items = suggestionsContainer.querySelectorAll('.suggestion-item');
        items.forEach((item, i) => {
            if (i === index) {
                item.style.backgroundColor = '#e3f2fd';
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.style.backgroundColor = '#fff';
            }
        });
    }
    
    function clearHighlight() {
        const items = suggestionsContainer.querySelectorAll('.suggestion-item');
        items.forEach(item => {
            item.style.backgroundColor = '#fff';
        });
    }
    
    document.addEventListener('click', function(e) {
        if (!addressInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });
})();

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
    
    function normalizeAddressForDisplay(address) {
        if (!address) return '';
        
        let normalized = address.replace(/\s+/g, ' ').trim();
        
        normalized = normalized.replace(/,/g, ' ');
        
        normalized = normalized.replace(/\b(?:ул\.?|улица|проспект|пр\.?|пр-т|переулок|пер\.?|бульвар|бул\.?|дом|д\.?)\s*/gi, '');
        
        normalized = normalized.replace(/[^\p{L}\p{N}]/gu, '');
        
        if (!/^[Сс]аратов/i.test(normalized) && /\d/.test(normalized) && normalized.length < 50) {
            normalized = 'Саратов' + normalized;
        }
        
        return normalized.trim();
    }
    
    function updateRequestUrls(address) {
        if (!requestUrlContainer || !address || address.trim().length < 5) {
            if (requestUrlContainer) {
                requestUrlContainer.style.display = 'none';
            }
            return;
        }
        
        const serverUrl = '{{ route("houses.get-coordinates") }}';
        const serverFullUrl = window.location.origin + serverUrl;
        if (serverRequestUrl) {
            serverRequestUrl.textContent = `POST ${serverFullUrl}`;
        }
        
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
    
    function resetIndicators() {
        loadingIcon.style.display = 'none';
        successIcon.style.display = 'none';
        errorIcon.style.display = 'none';
    }
    
    function fetchCoordinates(address) {
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
        
        updateRequestUrls(address);
        
        resetIndicators();
        loadingIcon.style.display = 'block';
        statusText.textContent = 'Определение координат...';
        statusText.style.color = '#2563eb';
        
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
    
    addressInput.addEventListener('input', function() {
        const address = this.value;
        
        updateRequestUrls(address);
        
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        
        debounceTimer = setTimeout(function() {
            fetchCoordinates(address);
        }, 800);
    });
    
    if (addressInput.value && addressInput.value.trim().length >= 5) {
        setTimeout(function() {
            fetchCoordinates(addressInput.value);
        }, 500);
    }
})();

(function() {
    const imagesInput = document.getElementById('images-input');
    const previewContainer = document.getElementById('preview-container');
    const deletedPhotosInput = document.getElementById('deleted-photos-input');
    let deletedPhotos = [];
    
    if (deletedPhotosInput && deletedPhotosInput.value) {
        try {
            deletedPhotos = JSON.parse(deletedPhotosInput.value);
        } catch(e) {
            deletedPhotos = [];
        }
    }
    
    if (imagesInput) {
        imagesInput.addEventListener('change', function(e) {
            previewContainer.innerHTML = '';
            previewContainer.classList.remove('show');
            
            const files = Array.from(e.target.files);
            if (files.length === 0) return;
            
            previewContainer.classList.add('show');
            
            files.forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'house-form-photo-item';
                    div.dataset.fileIndex = index;
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'house-form-photo-img';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'house-form-photo-delete remove-preview-btn';
                    removeBtn.textContent = '×';
                    removeBtn.onclick = function() {
                        const previewItems = Array.from(previewContainer.children);
                        const itemIndex = previewItems.indexOf(div);
                        
                        const dt = new DataTransfer();
                        const filesArray = Array.from(imagesInput.files);
                        if (itemIndex >= 0 && itemIndex < filesArray.length) {
                            filesArray.splice(itemIndex, 1);
                            filesArray.forEach(f => dt.items.add(f));
                            imagesInput.files = dt.files;
                        }
                        
                        div.remove();
                        
                        if (previewContainer.children.length === 0) {
                            previewContainer.classList.remove('show');
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
    
    document.querySelectorAll('.delete-photo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const photoId = this.dataset.photoId;
            const photoItem = this.closest('.photo-item');
            
            if (confirm('Удалить это фото?')) {
                if (!deletedPhotos.includes(photoId)) {
                    deletedPhotos.push(photoId);
                    deletedPhotosInput.value = JSON.stringify(deletedPhotos);
                }
                
                photoItem.style.opacity = '0.5';
                photoItem.style.pointerEvents = 'none';
                this.disabled = true;
            }
        });
    });
})();
</script>

@extends('layout')

@section('title')
    Карта домов
@endsection


@section('main_content')
    <div id="map"></div>
    
    <div class="overlay">
        <div class="panel" id="left-panel">
            <div class="panel-header">
                <span class="panel-title">Фильтры</span>
                <button class="toggle-btn" data-target="left-panel" data-side="left">❮</button>
            </div>
            <div class="panel-body">
                <div class="field">
                    <label for="search">Поиск по адресу</label>
                    <input type="text" id="search" placeholder="Введите адрес...">
                </div>

                <div class="field">
                    <label for="house_type">Тип дома</label>
                    <select id="house_type">
                        <option value="">Любой</option>
                    </select>
                </div>

                <div class="filters-row">
                    <div class="field">
                        <label for="price_min">Цена от</label>
                        <input type="number" id="price_min" placeholder="мин">
                    </div>
                    <div class="field">
                        <label for="price_max">Цена до</label>
                        <input type="number" id="price_max" placeholder="макс">
                    </div>
                </div>

                <div class="filters-row">
                    <div class="field">
                        <label for="area_min">Площадь от</label>
                        <input type="number" id="area_min" placeholder="мин">
                    </div>
                    <div class="field">
                        <label for="area_max">Площадь до</label>
                        <input type="number" id="area_max" placeholder="макс">
                    </div>
                </div>


                <button class="btn-reset" id="resetFilters">Сбросить фильтры</button>
            </div>
        </div>
        
        <div class="panel" id="middle-panel">
            <div class="panel-header">
                <span class="panel-title">Список домов</span>
                <button class="toggle-btn" data-target="middle-panel" data-side="left">❮</button>
            </div>
            <div class="panel-body">
                <div class="house-list" id="houseList"></div>
            </div>
        </div>

        <div class="map-spacer"></div>


        <div class="panel" id="right-panel">
            <div class="panel-header">
                <span class="panel-title">Информация о доме</span>
                <button class="toggle-btn" data-target="right-panel" data-side="right">❯</button>
            </div>
            <div class="panel-body">
                <div id="house-info">
                    <div id="house-info-empty">
                        Выберите дом в списке или на карте.
                    </div>
                </div>
            </div>
        </div>


    </div>
    
    <script src="https://api-maps.yandex.ru/2.1/?apikey=a2cd05de-c1e4-457b-8092-a8b0ebd9db10&lang=ru_RU" type="text/javascript"></script>
    <script>
        window.housesData = @json($houses);
        window.isAuthenticated = @json(auth()->check());
    </script>
    @vite(['resources/js/pages/map.js'])
@endsection
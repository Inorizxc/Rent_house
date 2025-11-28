@extends('layout')

@section('title', 'Дом #' . $house->house_id)

@section('main_content')
<div class="page-wrapper">
    <div class="container">
        <div class="left">
            <div class="title">
                {{ $house->adress ?? 'Адрес не указан' }}
            </div>
            <div class="subtitle">
                Дом #{{ $house->house_id }}
            </div>

            <div class="main-content">
                <div class="pictures">
                    @php
                        $photos = $house->photo ?? collect();
                    @endphp

                    @if($photos->count() > 0)
                        <img
                            src="{{ asset('storage/' . $photos->first()->path) }}"
                            alt="{{ $photos->first()->name }}"
                            class="pictures-main"
                            id="mainPhoto"
                        >

                        <div class="pictures-thumbs">
                            @foreach($photos as $photo)
                                <img
                                    src="{{ asset('storage/' . $photo->path) }}"
                                    alt="{{ $photo->name }}"
                                    data-full="{{ asset('storage/' . $photo->path) }}"
                                    onclick="document.getElementById('mainPhoto').src = this.dataset.full;"
                                >
                            @endforeach
                        </div>
                    @else
                        <div class="pictures-empty">
                            Нет фотографий для этого дома
                        </div>
                    @endif
                </div>

                <div class="section-title">О доме</div>

                <div class="description">
                    <div class="description-row">
                        <div class="description-label">Адрес</div>
                        <div class="description-value">{{ $house->adress ?? '—' }}</div>
                    </div>
                    <div class="description-row">
                        <div class="description-label">Площадь</div>
                        <div class="description-value">
                            {{ $house->area ? $house->area . ' м²' : '—' }}
                        </div>
                    </div>
                    <div class="description-row">
                        <div class="description-label">Тип аренды</div>
                        <div class="description-value">{{ $house->rent_type_id ?? '—' }}</div>
                    </div>
                    <div class="description-row">
                        <div class="description-label">Тип дома</div>
                        <div class="description-value">{{ $house->house_type_id ?? '—' }}</div>
                    </div>
                    <div class="description-row">
                        <div class="description-label">Координаты</div>
                        <div class="description-value">
                            {{ $house->lat && $house->lng ? $house->lat . ', ' . $house->lng : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="right">
            <div class="price">
                <div class="contact-block">
                    <div class="price-label">Стоимость</div>
                    <div class="price-value">
                        @if($house->price_id)
                            {{ number_format($house->price_id, 0, ',', ' ') }} ₽
                        @else
                            Не указана
                        @endif
                    </div>
                </div>
            </div>

            <div class="contact-block">
                <div class="contact-row">
                    <div class="contact-label">Контактное лицо</div>
                    <div class="contact-value">
                        @if(isset($house->user) && $house->user->user_id)
                            @php
                                $fio = trim(
                                    ($house->user->sename ?? '') . ' ' .
                                    ($house->user->name ?? '') . ' ' .
                                    ($house->user->patronymic ?? '')
                                );
                                $fio = $fio ?: 'Пользователь #' . $house->user->user_id;
                            @endphp
                            <a href="{{ route('profile.show', $house->user->user_id) }}">
                                {{ $fio }}
                            </a>
                        @else
                            Не указано
                        @endif
                    </div>
                </div>

                <div class="contact-row">
                    <div class="contact-label">Телефон</div>
                    <div class="contact-value">
                        {{ $house->user->phone ?? 'Не указан' }}
                    </div>
                </div>

                <div class="contact-row">
                    <div class="contact-label">Email</div>
                    <div class="contact-value">
                        {{ $house->user->email ?? 'Не указан' }}
                    </div>
                </div>

                <div class="actions">
                    @auth
                        <a href="{{ route('house.chat', $house->house_id) }}" class="btn-primary">
                            Оформление заказа
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary">
                            Войти для чата
                        </a>
                    @endauth

                    <a href="{{ route('map') }}#house-{{ $house->house_id }}" class="btn-secondary">
                        Показать на карте
                    </a>

                    <a href="{{ route('houses.index') }}" class="btn-secondary">
                        ← Назад к списку домов
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

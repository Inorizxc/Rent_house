@extends('layout')

@section('title', 'Дом #' . $house->house_id)

@section('style')
    body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f6f6f7;
    }

    .page-wrapper {
        padding: 10px 24px 24px; /* небольшой отступ сверху, так как body уже имеет padding-top */
        display: flex;
        justify-content: center;
    }

    .section-title {
        margin-top: 18px;
        margin-bottom: 10px;
        font-size: 17px;
        font-weight: 600;
        color: #1f2933;
        padding-bottom: 4px;
        border-bottom: 1px solid #e5e7eb;
    }
    .container {
        max-width: 1100px;
        width: 100%;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .left, .right {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e2e5;
        padding: 16px 18px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .subtitle {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .main-content {
        display: flex;
        flex-direction: column;
    }

    /* Галерея */
    .pictures {
        border-radius: 10px;
        overflow: hidden;
        background: #f3f4f6;
        border: 1px solid #e2e2e5;
    }

    .pictures-main {
        width: 100%;
        height: 600px;
        object-fit: cover;
        display: block;
    }

    .pictures-thumbs {
        display: flex;
        gap: 6px;
        padding: 8px;
        overflow-x: auto;
        background: #f9fafb;
    }

    .pictures-thumbs img {
        width: 64px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: border-color 0.15s, transform 0.1s;
    }

    .pictures-thumbs img:hover {
        border-color: #a5b4fc;
        transform: translateY(-1px);
    }

    .pictures-empty {
        height: 260px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 14px;
    }

    /* Описание */
    .description {
        font-size: 14px;
        color: #1f2933;
    }

    .description-row {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 6px;
        padding: 4px 0;
        border-bottom: 1px dashed #e5e7eb;
    }

    .description-row:last-child {
        border-bottom: none;
    }

    .description-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
    }

    .description-value {
        font-size: 14px;
        color: #111827;
    }

    /* Правая колонка */
    .right .price {
        margin-bottom: 16px;
    }

    /* ОБОЛОЧКА правого блока — фиксируется при скролле */
    .right {
        position: sticky;
        top: 67px; /* высота шапки + небольшой отступ */
        align-self: flex-start;
        height: auto;
    }

    /* Чтобы контент внутри не накладывался */
    .right .content-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .price-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .price-value {
        font-size: 22px;
        font-weight: 600;
        color: #111827;
    }

    .contact-block {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
        font-size: 14px;
    }

    .contact-row {
        margin-bottom: 4px;
    }

    .contact-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
    }

    .contact-value {
        font-size: 14px;
        color: #111827;
    }

    .contact-value a {
        color: #4f46e5;
        text-decoration: none;
        transition: color 0.2s, text-decoration 0.2s;
    }

    .contact-value a:hover {
        color: #4338ca;
        text-decoration: underline;
    }

    .actions {
        margin-top: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .btn-primary,
    .btn-secondary {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        transition: background 0.2s, border-color 0.2s, transform 0.1s;
    }

    .btn-primary {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #4338ca;
        border-color: #4338ca;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #ffffff;
        border-color: #e5e7eb;
        color: #111827;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        transform: translateY(-1px);
    }

    @media (max-width: 900px) {
        .container {
            grid-template-columns: 1fr;
        }

        .main-content {
            grid-template-columns: 1fr;
        }
    }
@endsection

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

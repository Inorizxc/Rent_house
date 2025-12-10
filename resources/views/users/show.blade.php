@extends('layout')

@section('title')
    Профиль
@endsection


@section('main_content')
    @php
        $currentUser = auth()->user();
        $isOwner = $currentUser && $currentUser->canEditProfile($user);
        $canViewProfile = !$currentUser || $currentUser->canViewProfile($user);
    @endphp

    <div class="profile-wrapper">
        <div class="profile-header">
            <div class="profile-avatar">
                😊
            </div>
            <div class="profile-header-info">
                <div class="profile-name">
                    {{ trim(($user->name ?? '') . ' ' . ($user->sename ?? '')) ?: 'Пользователь #'.$user->user_id }}
                </div>
                <div class="profile-rating">
                    <span>Оценка пока отсутствует</span>
                </div>
            </div>
        </div>

        <div class="profile-layout">
            <aside class="profile-sidebar">
                <div class="profile-sidebar-top">
                    <p><strong>Роль:</strong> {{ $user->roles->name }}</p>
                    {{-- Показываем email только владельцу --}}
                    @if($isOwner)
                        <p><strong>Почта:</strong> {{ $user->email ?? 'не указан' }}</p>
                    @else
                        <p><strong>Почта:</strong> скрыта</p>
                    @endif
                </div>

                @if($isOwner)
                    <div class="profile-sidebar-bottom">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="profile-sidebar-button">
                                Выйти из аккаунта
                            </button>
                        </form>
                    </div>
                @elseif(auth()->check() && auth()->id() != $user->user_id)
                    <div class="profile-sidebar-bottom">
                        <a href="{{ route('chats.start', $user->user_id) }}" class="profile-sidebar-button" style="display: block; text-align: center; text-decoration: none; padding: 10px;">
                            Написать сообщение
                        </a>
                    </div>
                @endif
            </aside>

            <section class="profile-main">
                <div class="profile-tabs">
                    <button class="profile-tab-btn active" data-tab="houses" data-route="{{ route('profile.tab.houses', $user->user_id) }}">Дома</button>
                    @if($isOwner)
                        <button class="profile-tab-btn" data-tab="orders" data-route="{{ route('profile.tab.orders', $user->user_id) }}">Заказы</button>
                    @endif
                    @if($isOwner)
                        <button class="profile-tab-btn" data-tab="settings" data-route="{{ route('profile.tab.settings', $user->user_id) }}">Настройки</button>
                    @endif
                    <div class="profile-tabs-spacer"></div>
                </div>

                <div class="profile-tab-panels" data-user-id="{{ $user->user_id }}">
                    <div class="profile-tab-panel active" id="tab-houses">
                        @include('users.partials.houses-tab', ['houses' => $houses, 'isOwner' => $isOwner])
                    </div>
                    @if($isOwner)
                        <div class="profile-tab-panel" id="tab-orders">
                            {{-- Контент загружается через AJAX --}}
                        </div>
                    @endif
                    @if($isOwner)
                        <div class="profile-tab-panel" id="tab-settings">
                            @include('users.partials.settings-tab')
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    @vite(['resources/js/pages/profile-tabs.js'])
@endsection

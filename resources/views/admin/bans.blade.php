@extends('layout')

@section('title', 'Управление банами - Админ-панель')

@section('main_content')
<div class="admin-container">
    @include('admin.partials.sidebar')
    
    <main class="admin-content" id="mainContent">
        <div class="content-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" type="button">☰</button>
                <h1>Управление банами</h1>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="ban-tabs">
            <button class="ban-tab {{ $type === 'users' ? 'active' : '' }}" onclick="window.location.href='{{ route('admin.bans', ['type' => 'users']) }}'">
                Пользователи
            </button>
            <button class="ban-tab {{ $type === 'houses' ? 'active' : '' }}" onclick="window.location.href='{{ route('admin.bans', ['type' => 'houses']) }}'">
                Дома
            </button>
        </div>

        <div class="admin-card">
            <form method="GET" action="{{ route('admin.bans') }}" class="rows-per-page">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="page" value="1">
                <label for="per">{{ $type === 'users' ? 'Пользователей' : 'Домов' }} на странице:</label>
                <input
                    id="per"
                    name="per"
                    type="number"
                    min="1"
                    max="100"
                    value="{{ $limit }}"
                    onchange="this.form.submit()"
                >
            </form>

            @if ($type === 'users')
                @include('admin.partials.bans-users-table')
            @else
                @include('admin.partials.bans-houses-table')
            @endif
        </div>
    </main>
</div>

@include('admin.partials.ban-modals')
@include('admin.partials.admin-scripts')
@endsection


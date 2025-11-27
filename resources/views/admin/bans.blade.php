@extends('layout')

@section('title', 'Управление банами - Админ-панель')

@section('style')
    @include('admin.partials.admin-styles')
    
    .ban-tabs {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e5e5e5;
    }
    
    .ban-tab {
        padding: 12px 24px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.2s;
    }
    
    .ban-tab:hover {
        color: #667eea;
    }
    
    .ban-tab.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }
    
    .ban-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .ban-status.banned {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .ban-status.not-banned {
        background: #d1fae5;
        color: #065f46;
    }
    
    .ban-status.deleted {
        background: #f3f4f6;
        color: #6b7280;
    }
    
    .ban-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .ban-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }
    
    .ban-modal.show {
        display: flex;
    }
    
    .modal-content {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        margin-bottom: 20px;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #1c1c1c;
    }
    
    .modal-body {
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-group input[type="radio"] {
        width: auto;
        margin-right: 8px;
    }
    
    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .radio-option {
        display: flex;
        align-items: center;
    }
    
    .radio-option label {
        margin: 0;
        font-weight: 400;
        cursor: pointer;
    }
    
    .modal-footer {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }
    
    .house-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
@endsection

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


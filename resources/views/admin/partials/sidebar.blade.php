<aside class="admin-sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Админ-панель</h2>
        <button class="sidebar-toggle" id="sidebarToggle" type="button">✕</button>
    </div>
    <nav class="table-list">
        <a href="{{ route('admin.chats') }}" 
           class="table-item {{ request()->routeIs('admin.chats*') ? 'active' : '' }}">
            📨 Все чаты
        </a>
        <a href="{{ route('admin.orders') }}" 
           class="table-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
            📦 Все заказы
        </a>
        <a href="{{ route('admin.verification') }}" 
           class="table-item {{ request()->routeIs('admin.verification*') ? 'active' : '' }}">
            ✅ Верификация
        </a>
        <a href="{{ route('admin.bans') }}" 
           class="table-item {{ request()->routeIs('admin.bans*') ? 'active' : '' }}">
            🚫 Баны
        </a>
        <div style="padding: 12px 20px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-top: 1px solid #e5e5e5; margin-top: 8px; padding-top: 16px;">
            Таблицы БД
        </div>
        @php
            $tableNames = collect(\Illuminate\Support\Facades\DB::select("
                SELECT name
                FROM sqlite_master
                WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
                ORDER BY name
            "))->pluck('name');
        @endphp
        @foreach ($tableNames as $table)
            <a href="{{ route('admin.panel', ['table' => $table]) }}" 
               class="table-item">
                {{ $table }}
            </a>
        @endforeach
    </nav>
</aside>


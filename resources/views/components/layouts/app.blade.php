{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Админка</title>

    {{-- Если используешь Vite – раскомментируй строку ниже и убедись, что есть resources/css/js --}}
    {{-- @vite(['resources/css/app.css','resources/js/app.js']) --}}

    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <nav class="p-4 border-b bg-white">
        <a href="/" class="font-semibold">Главная</a>
        <a href="{{ route('users') }}" class="ml-4">Пользователи</a>
    </nav>

    <main class="p-6">
        {{ $slot ?? '' }}   {{-- ВАЖНО: Livewire будет рендерить сюда содержимое страницы --}}
    </main>

    @livewireScripts
    {{-- Alpine для модалки (если используешь) --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>

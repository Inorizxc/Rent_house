<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name', 'Сайт аренды домов') }}</title>

    {{-- Подключаем общий и кастомный CSS через Vite --}}
    @vite([
        'resources/css/users.css'
    ])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="min-h-screen flex flex-col">
        {{-- Основное содержимое страниц Livewire --}}
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
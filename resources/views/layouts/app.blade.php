<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Мой сайт')</title>

    {{-- подключение css/js --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">
    <header class="p-4 bg-white shadow">
        <h1 class="text-xl font-semibold">ZloVito</h1>
    </header>

    <main class="p-6">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Houses</title>

    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-[#0f0f0f] text-white min-h-screen">
    <div class="container mx-auto py-6">
        @yield('content')
    </div>

    @livewireScripts
    <script defer src="//unpkg.com/alpinejs"></script>
</body>
</html>

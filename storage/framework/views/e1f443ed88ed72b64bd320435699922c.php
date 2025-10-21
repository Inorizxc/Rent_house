
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Админка</title>

    
    

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <nav class="p-4 border-b bg-white">
        <a href="/" class="font-semibold">Главная</a>
        <a href="<?php echo e(route('users')); ?>" class="ml-4">Пользователи</a>
    </nav>

    <main class="p-6">
        <?php echo e($slot ?? ''); ?>   
    </main>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
<?php /**PATH G:\RentHouse\Rent_house\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>
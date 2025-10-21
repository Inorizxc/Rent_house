<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo e(config('app.name', 'Сайт аренды домов')); ?></title>

    
    <?php echo app('Illuminate\Foundation\Vite')([
        'resources/css/users.css'
    ]); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="min-h-screen flex flex-col">
        
        <?php echo e($slot); ?>

    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html><?php /**PATH G:\RentHouse\Rent_house\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>
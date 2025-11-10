<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Houses</title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="bg-[#0f0f0f] text-white min-h-screen">
    <div class="container mx-auto py-6">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <script defer src="//unpkg.com/alpinejs"></script>
</body>
</html>
<?php /**PATH G:\RentHouse\Rent_house\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>
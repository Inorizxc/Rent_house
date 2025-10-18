<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр таблиц SQLite</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-5xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Просмотр таблиц SQLite</h1>

        <!-- Меню выбора таблицы -->
        <form method="GET" action="/">
            <label for="table" class="block mb-2 font-semibold">Выберите таблицу:</label>
            <select id="table" name="table" onchange="this.form.submit()"
                    class="w-full border rounded px-3 py-2">
                <option value="">-- выберите таблицу --</option>
                <?php $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($table); ?>"
                        <?php echo e($selectedTable === $table ? 'selected' : ''); ?>>
                        <?php echo e($table); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>

        <!-- Вывод таблицы -->
        <?php if($selectedTable): ?>
            <h2 class="text-xl font-semibold mt-6 mb-3">Таблица: <?php echo e($selectedTable); ?></h2>

            <?php if($rows->isEmpty()): ?>
                <p class="text-gray-500">Таблица пуста или не содержит данных.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 rounded-lg">
                        <thead class="bg-gray-200">
                            <tr>
                                <?php $__currentLoopData = array_keys((array)$rows->first()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="border px-3 py-2 text-left text-sm font-semibold"><?php echo e($col); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="odd:bg-white even:bg-gray-50">
                                    <?php $__currentLoopData = (array)$row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="border px-3 py-2 text-sm">
                                            <?php echo e(is_null($val) ? '—' : $val); ?>

                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</body>

</html><?php /**PATH C:\Users\MSI_PC\Desktop\fdjfifewfw\Rent_house\resources\views/test.blade.php ENDPATH**/ ?>
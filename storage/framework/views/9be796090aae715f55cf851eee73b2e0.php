<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>SQLite Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-6xl mx-auto space-y-6">

    <div class="bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-4">Выбор таблицы</h1>

        <?php if(session('status')): ?>
            <div class="mb-4 rounded border border-green-200 bg-green-50 text-green-800 px-4 py-2">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <form method="GET" action="/">
            <label for="table" class="block mb-2 font-semibold">Таблица:</label>
            <select id="table" name="table" class="w-full border rounded px-3 py-2" onchange="this.form.submit()">
                <option value="">— выберите таблицу —</option>
                <?php $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($table); ?>" <?php echo e(old('table', $selectedTable) === $table ? 'selected' : ''); ?>>
                        <?php echo e($table); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
    </div>

    <?php if($selectedTable): ?>
        
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-xl font-semibold mb-4">Добавить запись в «<?php echo e($selectedTable); ?>»</h2>

            <form method="POST" action="/">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="table" value="<?php echo e($selectedTable); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php
                        $blocked = ['id','created_at','updated_at','deleted_at'];
                    ?>

                    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(in_array($col->name, $blocked, true)) continue; ?>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                <?php echo e($col->name); ?>

                                <span class="text-xs text-gray-500">
                                    (<?php echo e($col->type ?: 'TEXT'); ?>)
                                    <?php if($col->notnull): ?> • required <?php endif; ?>
                                </span>
                            </label>
                            <input
                                type="text"
                                name="<?php echo e($col->name); ?>"
                                value="<?php echo e(old($col->name)); ?>"
                                class="w-full border rounded px-3 py-2"
                                <?php if($col->notnull && $col->dflt_value === null): ?> required <?php endif; ?>
                                placeholder="Введите значение"
                            >
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-4">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Добавить запись
                    </button>
                </div>
            </form>
        </div>

        
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-xl font-semibold mb-3">Первые 10 строк из «<?php echo e($selectedTable); ?>»</h2>

            <?php if($rows->isEmpty()): ?>
                <p class="text-gray-500">Нет данных для отображения.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 rounded">
                        <thead>
                            <tr class="bg-gray-200">
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
        </div>
    <?php endif; ?>

</div>
</body>

</html><?php /**PATH C:\Users\MSI_PC\Desktop\fdjfifewfw\Rent_house\resources\views/test.blade.php ENDPATH**/ ?>
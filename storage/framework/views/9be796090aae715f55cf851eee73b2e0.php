<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>SQLite Viewer</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/test.css']); ?>
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
            
<?php
    $p = $page;   // текущая страница
    $N = $pages;  // всего страниц

    // Формируем ровно те элементы, что нужно вывести (без дублей)
    // Модели:
    //  - число (int)
    //  - 'dots' — одно многоточие (слева или справа)
    $items = [];

    if ($N <= 7) {
        // мало страниц — показываем все
        $items = range(1, $N);
    } elseif ($p <= 4) {
        // начало: 1 2 3 4 5 … N
        $items = [1, 2, 3, 4, 5, 'dots', $N];
    } elseif ($p >= $N - 3) {
        // конец: 1 … N-4 N-3 N-2 N-1 N
        $items = [1, 'dots', $N-4, $N-3, $N-2, $N-1, $N];
    } else {
        // середина: 1 … p-1 p p+1 … N
        $items = [1, 'dots', $p-1, $p, $p+1, 'dots', $N];
    }
?>

<div class="pagination-container">
  <div class="pagination">
    
    <a href="?table=<?php echo e(urlencode($selectedTable)); ?>&page=<?php echo e(max(1, $p-1)); ?>"
       class="pag-btn <?php echo e($p==1 ? 'pag-disabled' : ''); ?>"
       aria-label="Назад">&lsaquo;</a>

    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($it === 'dots'): ?>
            <button type="button" class="pag-ellipsis" data-total="<?php echo e($N); ?>">…</button>
        <?php else: ?>
            <a href="?table=<?php echo e(urlencode($selectedTable)); ?>&page=<?php echo e($it); ?>"
               class="pag-num <?php echo e($it==$p ? 'pag-active' : ''); ?>"><?php echo e($it); ?></a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <a href="?table=<?php echo e(urlencode($selectedTable)); ?>&page=<?php echo e(min($N, $p+1)); ?>"
       class="pag-btn <?php echo e($p==$N ? 'pag-disabled' : ''); ?>"
       aria-label="Следующая страница">&rsaquo;</a>
  </div>
</div>
<?php endif; ?>
    </div>
 <?php endif; ?>

</div>


<script>
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('pag-ellipsis')) {
        const total = parseInt(e.target.getAttribute('data-total') || '1', 10);
        const input = prompt(`Введите номер страницы (1–${total})`);
        if (!input) return;
        let p = parseInt(input, 10);
        if (isNaN(p)) { alert('Введите число.'); return; }
        if (p < 1) p = 1;
        if (p > total) p = total;

        const params = new URLSearchParams(window.location.search);
        params.set('table', <?php echo json_encode($selectedTable, 15, 512) ?>);
        params.set('page', String(p));
        window.location.search = params.toString();
    }
});
</script>
</body>
</html><?php /**PATH C:\Users\MSI_PC\Desktop\fdjfifewfw\Rent_house\resources\views/test.blade.php ENDPATH**/ ?>
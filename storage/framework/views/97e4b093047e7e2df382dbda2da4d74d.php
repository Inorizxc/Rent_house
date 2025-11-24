

<div class="p-6">
    <!--[if BLOCK]><![endif]--><?php if(session()->has('ok')): ?>
        <div class="mb-4 rounded border px-4 py-2 bg-green-50 text-green-800">
            <?php echo e(session('ok')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-2">
            <input type="text"
                   wire:model.defer="searchInput"
                   wire:keydown.enter="applySearch"
                   placeholder="Поиск по адресу/площади"
                   class="border rounded px-3 py-2 w-72">
            <button wire:click="applySearch" class="px-4 py-2 border rounded">Искать</button>

            <!--[if BLOCK]><![endif]--><?php if($search): ?>
                <button class="px-3 py-2 text-sm underline"
                        wire:click="$set('searchInput',''); $set('search','');">Сброс</button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <?php
            $currentUser = auth()->user();
            $canCreateHouse = $currentUser && $currentUser->canCreateHouse();
        ?>
        <!--[if BLOCK]><![endif]--><?php if($canCreateHouse): ?>
            <a href="<?php echo e(route('houses.create')); ?>" class="px-4 py-2 rounded bg-blue-600 text-white">
                Добавить дом
            </a>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-xl border bg-white shadow overflow-hidden flex flex-col" wire:key="card-<?php echo e($h->house_id); ?>">
                <div class="aspect-[4/3] bg-gray-100">
                    <img src="<?php echo e($h->image_url); ?>" alt="Фото дома #<?php echo e($h->house_id); ?>"
                         class="w-full h-full object-cover">
                </div>

                <div class="p-4 flex-1 flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold truncate"><?php echo e($h->adress); ?></h3>
                        <!--[if BLOCK]><![endif]--><?php if($h->is_deleted): ?>
                            <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">Удалён</span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-gray-600">
                        <div>
                            <dt class="text-gray-500">Площадь</dt>
                            <dd><?php echo e($h->area ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Цена ID</dt>
                            <dd><?php echo e($h->price_id ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Тип аренды</dt>
                            <dd><?php echo e($h->rent_type_id ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Тип дома</dt>
                            <dd><?php echo e($h->house_type_id ?? '—'); ?></dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Координаты</dt>
                            <dd class="font-mono text-xs"><?php echo e($h->lat); ?>, <?php echo e($h->lng); ?></dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Пользователь</dt>
                            <dd>
                                <!--[if BLOCK]><![endif]--><?php if($h->user): ?>
                                    <?php echo e(trim(($h->user->sename ?? '').' '.($h->user->name ?? '').' '.($h->user->patronymic ?? '')) ?: ('User #'.$h->user_id)); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </dd>
                        </div>
                    </dl>

                    <?php
                        $canEditThisHouse = $currentUser && $currentUser->canEditHouse($h);
                        $canDeleteThisHouse = $currentUser && $currentUser->canDeleteHouse($h);
                    ?>
                    <!--[if BLOCK]><![endif]--><?php if($canEditThisHouse || $canDeleteThisHouse): ?>
                        <div class="mt-4 flex items-center justify-between gap-2">
                            <!--[if BLOCK]><![endif]--><?php if($canEditThisHouse): ?>
                                <a href="<?php echo e(route('houses.edit', $h)); ?>" class="px-3 py-2 border rounded">Редактировать</a>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                            <!--[if BLOCK]><![endif]--><?php if($canDeleteThisHouse): ?>
                                <form method="POST" action="<?php echo e(route('houses.destroy', $h)); ?>"
                                      onsubmit="return confirm('Удалить дом #<?php echo e($h->house_id); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="px-3 py-2 border rounded text-red-600">
                                        Удалить
                                    </button>
                                </form>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full text-center text-gray-500 py-10">
                Записей нет
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <div class="mt-6">
        <?php echo e($houses->links()); ?>

    </div>
</div>
<?php /**PATH C:\Users\MSI_PC\Desktop\fdjfifewfw\Rent_house\resources\views/livewire/houses-page.blade.php ENDPATH**/ ?>
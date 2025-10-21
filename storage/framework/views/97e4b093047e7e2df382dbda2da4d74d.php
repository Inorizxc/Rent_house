
<div class="users-page max-w-[1500px] mx-auto p-6">

    
    <div class="toolbar flex items-center justify-between mb-4 gap-4">
        <div class="flex gap-2 w-full">
            <input
                type="text"
                wire:model.defer="searchInput"
                class="input flex-grow"
                placeholder="Поиск домов (адрес, площадь)…"
            />
            <button class="btn btn-primary" wire:click="applySearch">Поиск</button>
            <button class="btn btn-accent" wire:click="startCreate">+ Новый дом</button>
        </div>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if(session('ok')): ?>
        <div class="alert alert-success mb-3">
            <?php echo e(session('ok')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if(!is_null($editingId)): ?>
        <div class="card mb-6 p-5">
            <div class="section-title mb-3">
                <?php echo e($editingId === 0 ? 'Создание дома' : 'Редактирование дома'); ?>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Адрес</label>
                        <input type="text" class="input" wire:model.defer="form.adress">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.adress'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Площадь (м²)</label>
                        <input type="number" step="0.01" class="input" wire:model.defer="form.area">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Тип аренды (rent_type_id)</label>
                        <input type="number" class="input" wire:model.defer="form.rent_type_id">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.rent_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Тип дома (house_type_id)</label>
                        <input type="number" class="input" wire:model.defer="form.house_type_id">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.house_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Пользователь (user_id)</label>
                        <select class="input" wire:model.defer="form.user_id">
                            <option value="">— не выбран —</option>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($u->user_id); ?>"><?php echo e($u->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Календарь (calendar_id)</label>
                        <input type="number" class="input" wire:model.defer="form.calendar_id">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.calendar_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Долгота (lng)</label>
                        <input type="number" step="0.000001" class="input" wire:model.defer="form.lng">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.lng'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Широта (lat)</label>
                        <input type="number" step="0.000001" class="input" wire:model.defer="form.lat">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.lat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div>
                        <label class="block mb-1 text-sm text-gray-400">Статус</label>
                        <select class="input" wire:model.defer="form.is_deleted">
                            <option value="0">Активен</option>
                            <option value="1">Удалён</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.is_deleted'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                
                <div class="space-y-3">
                    <label class="block mb-1 text-sm text-gray-400">Изображение</label>

                    
                    <div class="rounded-xl overflow-hidden border border-[#2a2a2a] bg-[#0f0f0f]">
                        <?php
                            $img = null;
                            if (!is_null($editingId)) {
                                $try = ['jpg','jpeg','png','webp','gif'];
                                foreach ($try as $ext) {
                                    $p = public_path('storage/houses/'.$editingId.'.'.$ext);
                                    if (file_exists($p)) { $img = asset('storage/houses/'.$editingId.'.'.$ext); break; }
                                }
                            }
                        ?>
                        <!--[if BLOCK]><![endif]--><?php if($imageTmp): ?>
                            <img src="<?php echo e($imageTmp->temporaryUrl()); ?>" class="w-full h-48 object-cover" alt="">
                        <?php elseif($img): ?>
                            <img src="<?php echo e($img); ?>" class="w-full h-48 object-cover" alt="">
                        <?php else: ?>
                            <div class="w-full h-48 flex items-center justify-center text-[#1DB954]/80">
                                
                                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <path d="M3 15l4-4 3 3 5-5 6 6"></path>
                                    <circle cx="9" cy="7.5" r="1.5"></circle>
                                </svg>
                                <span class="ml-2 text-sm text-gray-400">Нет фото</span>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <input type="file" class="input" wire:model="imageTmp" accept="image/*">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['imageTmp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <button class="btn btn-success" wire:click="save">Сохранить</button>
                <button class="btn btn-ghost" wire:click="cancelEdit">Отмена</button>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                // пробуем найти физический файл: storage/houses/{id}.{ext}
                $img = null;
                $try = ['jpg','jpeg','png','webp','gif'];
                foreach ($try as $ext) {
                    $p = public_path('storage/houses/'.$h->house_id.'.'.$ext);
                    if (file_exists($p)) { $img = asset('storage/houses/'.$h->house_id.'.'.$ext); break; }
                }
            ?>

            <div class="card overflow-hidden">
                
                <!--[if BLOCK]><![endif]--><?php if($img): ?>
                    <img src="<?php echo e($img); ?>" alt="Дом #<?php echo e($h->house_id); ?>" class="w-full h-44 object-cover">
                <?php else: ?>
                    <div class="w-full h-44 flex items-center justify-center bg-[#0f0f0f] border-b border-[#2a2a2a] text-[#1DB954]/80">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <path d="M3 15l4-4 3 3 5-5 6 6"></path>
                            <circle cx="9" cy="7.5" r="1.5"></circle>
                        </svg>
                        <span class="ml-2 text-sm text-gray-400">Нет фото</span>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <div class="p-4">
                    <div class="text-lg font-extrabold mb-1">
                        <?php echo e($h->adress ?: 'Адрес не указан'); ?>

                    </div>

                    <div class="text-sm text-gray-400 mb-2">
                        <!--[if BLOCK]><![endif]--><?php if($h->house_type): ?> Тип: <?php echo e($h->house_type->name ?? $h->house_type_id); ?> • <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if($h->rent_type): ?> Аренда: <?php echo e($h->rent_type->name ?? $h->rent_type_id); ?> • <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if(!is_null($h->area)): ?>
                            Площадь:
                            <?php echo e(is_numeric($h->area)
                                ? rtrim(rtrim(number_format((float)$h->area, 2, '.', ' '), '0'), '.')
                                : $h->area); ?> м²
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if($h->lng && $h->lat): ?>
                        <div class="text-xs text-gray-500">Координаты: <?php echo e($h->lat); ?>, <?php echo e($h->lng); ?></div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-300">
                        <!--[if BLOCK]><![endif]--><?php if($h->user): ?>
                            <span class="px-2 py-1 rounded-full bg-[#1E1E1E] border border-[#2a2a2a]">🙋 <?php echo e($h->user->name); ?></span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <span class="px-2 py-1 rounded-full bg-[#1E1E1E] border border-[#2a2a2a]">ID: <?php echo e($h->house_id); ?></span>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <button class="btn btn-accent" disabled>Заказать</button>
                        <div class="flex gap-2">
                            <button class="btn btn-primary" wire:click="startEdit(<?php echo e($h->house_id); ?>)">✏️</button>
                            <button class="btn btn-ghost text-red-500"
                                    onclick="return confirm('Удалить дом?')"
                                    wire:click="delete(<?php echo e($h->house_id); ?>)">🗑️</button>
                        </div>
                    </div>
                </div>
            </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full">
                <div class="card p-6 text-center text-gray-400">Домов пока нет. Добавь первый дом!</div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    </div>

    
    <div class="mt-6 pagination-wrap">
        <?php echo e($houses->links()); ?>

    </div>
</div>
<?php /**PATH C:\Users\MSI_PC\Desktop\fdjfifewfw\Rent_house\resources\views/livewire/houses-page.blade.php ENDPATH**/ ?>
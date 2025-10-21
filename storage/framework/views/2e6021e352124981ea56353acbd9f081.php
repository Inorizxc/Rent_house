<div class="max-w-[1400px] mx-auto p-6">
    <div class="flex items-center justify-between gap-4 mb-4">
        <input type="text" wire:model.debounce.400ms="search"
               class="border rounded px-3 py-2 w-full"
               placeholder="Поиск (имя, email, телефон)…">
        <button class="bg-indigo-600 text-white rounded px-4 py-2"
                wire:click="create">+ Новый</button>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session('ok')): ?>
        <div class="bg-green-100 text-green-800 px-3 py-2 rounded mb-3">
            <?php echo e(session('ok')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="overflow-x-auto">
    <table class="min-w-full text-sm border">
        <thead class="bg-gray-50">
        <tr>
            <th class="p-2 border">ID</th>
            <th class="p-2 border">Роль</th>
            <th class="p-2 border">Имя</th>
            <th class="p-2 border">Фамилия</th>
            <th class="p-2 border">Отчество</th>
            <th class="p-2 border">Дата рождения</th>
            <th class="p-2 border">Email</th>
            <th class="p-2 border">Телефон</th>
            <th class="p-2 border">Карта</th>
            <th class="p-2 border w-32">Действия</th>
        </tr>
        </thead>
        <tbody>
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-t hover:bg-gray-50">
                <td class="p-2 border"><?php echo e($u->user_id); ?></td>
                <td class="p-2 border"><?php echo e($u->roles->name ?? '—'); ?></td>
                <td class="p-2 border"><?php echo e($u->name); ?></td>
                <td class="p-2 border"><?php echo e($u->sename); ?></td>
                <td class="p-2 border"><?php echo e($u->patronymic); ?></td>
                <td class="p-2 border"><?php echo e(optional($u->birth_date)->format('Y-m-d')); ?></td>
                <td class="p-2 border"><?php echo e($u->email); ?></td>
                <td class="p-2 border"><?php echo e($u->phone); ?></td>
                <td class="p-2 border"><?php echo e($u->card); ?></td>
                <td class="p-2 border text-right">
                    <button class="text-indigo-600" wire:click="edit(<?php echo e($u->user_id); ?>)">✏️</button>
                    <button class="text-red-600 ml-3" wire:click="delete(<?php echo e($u->user_id); ?>)"
                            onclick="return confirm('Удалить пользователя?')">🗑️</button>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </tbody>
    </table>
    </div>

    <div class="mt-3"><?php echo e($users->links()); ?></div>

    
    <div x-data="{open:false}" x-show="open" x-cloak
         @open-form.window="open=true" @close-form.window="open=false"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
            <h2 class="text-lg font-semibold mb-4">
                <?php echo e($editingId ? 'Редактировать пользователя' : 'Новый пользователь'); ?>

            </h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Роль</label>
                    <select wire:model.defer="role_id" class="border rounded w-full px-2 py-1">
                        <option value="">— выбери —</option>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r->role_id); ?>"><?php echo e($r->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <div>
                    <label class="block text-sm mb-1">Имя</label>
                    <input type="text" wire:model.defer="name" class="border rounded w-full px-2 py-1">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <div>
                    <label class="block text-sm mb-1">Фамилия</label>
                    <input type="text" wire:model.defer="sename" class="border rounded w-full px-2 py-1">
                </div>

                <div>
                    <label class="block text-sm mb-1">Отчество</label>
                    <input type="text" wire:model.defer="patronymic" class="border rounded w-full px-2 py-1">
                </div>

                <div>
                    <label class="block text-sm mb-1">Дата рождения</label>
                    <input type="date" wire:model.defer="birth_date" class="border rounded w-full px-2 py-1">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['birth_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" wire:model.defer="email" class="border rounded w-full px-2 py-1">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <div>
                    <label class="block text-sm mb-1">Телефон</label>
                    <input type="text" wire:model.defer="phone" class="border rounded w-full px-2 py-1">
                </div>

                <div>
                    <label class="block text-sm mb-1">Карта</label>
                    <input type="text" wire:model.defer="card" class="border rounded w-full px-2 py-1">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm mb-1">Пароль
                        <span class="text-gray-500 text-xs">(при редактировании оставь пустым, если не меняешь)</span>
                    </label>
                    <input type="password" wire:model.defer="password" class="border rounded w-full px-2 py-1">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button class="px-4 py-2 rounded border"
                        @click="open=false; window.dispatchEvent(new CustomEvent('close-form'))">Отмена</button>
                <button class="px-4 py-2 rounded bg-indigo-600 text-white" wire:click="save">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH G:\RentHouse\Rent_house\resources\views/livewire/users-page.blade.php ENDPATH**/ ?>
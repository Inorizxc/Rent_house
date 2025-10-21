<div class="max-w-[1500px] mx-auto p-6">
    <div class="flex items-center justify-between mb-4 gap-4">
        <input type="text" wire:model.debounce.400ms="search"
               class="border rounded px-3 py-2 w-full"
               placeholder="Поиск (имя, email, телефон)…">
        <button class="bg-indigo-600 text-white rounded px-4 py-2" wire:click="startCreate">
            + Новый
        </button>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session('ok')): ?>
        <div class="bg-green-100 text-green-800 px-3 py-2 rounded mb-3">
            <?php echo e(session('ok')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="overflow-x-auto">
    <table class="min-w-full border text-sm">
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
            <th class="p-2 border">Пароль</th>
            <th class="p-2 border w-40">Действия</th>
        </tr>
        </thead>

        <tbody>
        
        <!--[if BLOCK]><![endif]--><?php if($editingRowId === 0): ?>
            <tr class="border-t bg-yellow-50">
                <td class="p-2 border">—</td>

                
                <td class="p-2 border">
                    <select wire:model.defer="row.role_id"
                            wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                            class="border rounded px-2 py-1 w-full">
                        <option value="">— выбери —</option>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r->role_id); ?>"><?php echo e($r->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.name"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.sename"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                </td>

                
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.patronymic"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                </td>

                
                <td class="p-2 border">
                    <input type="date" wire:model.defer="row.birth_date"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.birth_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <input type="email" wire:model.defer="row.email"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.phone"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                </td>

                
                <td class="p-2 border">
                    <input type="text" wire:model.defer="row.card"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full">
                </td>

                
                <td class="p-2 border">
                    <input type="password" wire:model.defer="passwordNew"
                           wire:keydown.enter="saveRow" class="border rounded px-2 py-1 w-full"
                           placeholder="Новый пароль">
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['passwordNew'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                <td class="p-2 border">
                    <button class="bg-green-600 text-white px-3 py-1 rounded" wire:click="saveRow">Создать</button>
                    <button class="ml-2 border px-3 py-1 rounded" wire:click="cancelEdit">Отмена</button>
                </td>
            </tr>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $isRow = $editingRowId === $u->user_id; ?>
            <tr class="border-t hover:bg-gray-50">
                <td class="p-2 border"><?php echo e($u->user_id); ?></td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='role_id'): ?>
                        <select wire:model.defer="row.role_id"
                                wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                                class="border rounded px-2 py-1 w-full">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($r->role_id); ?>"><?php echo e($r->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="cursor-pointer"
                             wire:click="setField(<?php echo e($u->user_id); ?>, 'role_id')">
                            <?php echo e($isRow ? (optional($roles->firstWhere('role_id',$row['role_id']))->name) : ($u->roles->name ?? '—')); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='name'): ?>
                        <input type="text" wire:model.defer="row.name"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="cursor-pointer" wire:click="setField(<?php echo e($u->user_id); ?>, 'name')">
                            <?php echo e($isRow ? $row['name'] : $u->name); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='sename'): ?>
                        <input type="text" wire:model.defer="row.sename"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                    <?php else: ?>
                        <div class="cursor-pointer" wire:click="setField(<?php echo e($u->user_id); ?>, 'sename')">
                            <?php echo e($isRow ? $row['sename'] : $u->sename); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='patronymic'): ?>
                        <input type="text" wire:model.defer="row.patronymic"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                    <?php else: ?>
                        <div class="cursor-pointer" wire:click="setField(<?php echo e($u->user_id); ?>, 'patronymic')">
                            <?php echo e($isRow ? $row['patronymic'] : $u->patronymic); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='birth_date'): ?>
                        <input type="date" wire:model.defer="row.birth_date"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.birth_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="cursor-pointer" wire:click="setField(<?php echo e($u->user_id); ?>, 'birth_date')">
                            <?php echo e($isRow ? $row['birth_date'] : optional($u->birth_date)->format('Y-m-d')); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='email'): ?>
                        <input type="email" wire:model.defer="row.email"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="cursor-pointer" wire:click="setField(<?php echo e($u->user_id); ?>, 'email')">
                            <?php echo e($isRow ? $row['email'] : $u->email); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='phone'): ?>
                        <input type="text" wire:model.defer="row.phone"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                    <?php else: ?>
                        <div class="cursor-pointer" wire:click="setField(<?php echo e($u->user_id); ?>, 'phone')">
                            <?php echo e($isRow ? $row['phone'] : $u->phone); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='card'): ?>
                        <input type="text" wire:model.defer="row.card"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full">
                    <?php else: ?>
                        <div class="cursor-pointer" wire:click="setField(<?php echo e($u->user_id); ?>, 'card')">
                            <?php echo e($isRow ? $row['card'] : $u->card); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                
                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='password'): ?>
                        <input type="password" wire:model.defer="passwordNew"
                               wire:keydown.enter="saveRow" wire:blur="saveRow" autofocus
                               class="border rounded px-2 py-1 w-full" placeholder="Новый пароль">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['passwordNew'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-xs"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="cursor-pointer text-blue-600" wire:click="setField(<?php echo e($u->user_id); ?>, 'password')">
                            задать
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>

                <td class="p-2 border">
                    <!--[if BLOCK]><![endif]--><?php if(!$isRow): ?>
                        <button class="text-indigo-600" wire:click="startEdit(<?php echo e($u->user_id); ?>)">✏️</button>
                        <button class="text-red-600 ml-3" wire:click="deleteUser(<?php echo e($u->user_id); ?>)"
                                onclick="return confirm('Удалить пользователя?')">🗑️</button>
                    <?php else: ?>
                        <button class="bg-green-600 text-white px-3 py-1 rounded" wire:click="saveRow">Сохранить</button>
                        <button class="ml-2 border px-3 py-1 rounded" wire:click="cancelEdit">Отмена</button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </tbody>
    </table>
    </div>

    <div class="mt-3"><?php echo e($users->links()); ?></div>
</div>
<?php /**PATH G:\RentHouse\Rent_house\resources\views/livewire/users-page.blade.php ENDPATH**/ ?>

<div class="users-page max-w-[1500px] mx-auto p-6">

    
    <div class="flex items-center justify-between mb-4 gap-4">
        <div class="flex gap-2 w-full">
            <input
                type="text"
                wire:model.defer="searchInput"
                class="input flex-grow"
                placeholder="Поиск (имя, email, телефон)…"
            />

            <button class="btn btn-primary" wire:click="applySearch">
                Поиск
            </button>

            <button class="btn btn-accent" wire:click="startCreate">
                Создать пользователя
            </button>
        </div>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if(session('ok')): ?>
        <div class="alert alert-success mb-3">
            <?php echo e(session('ok')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <div class="overflow-x-auto card">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Роль</th>
                    <th>Имя</th>
                    <th>Фамилия</th>
                    <th>Отчество</th>
                    <th>Дата рождения</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Карта</th>
                    <th>Пароль</th>
                    <th class="w-40">Действие</th>
                </tr>
            </thead>

            <tbody>
                
                <!--[if BLOCK]><![endif]--><?php if($editingRowId === 0): ?>
                    <tr class="is-editing">
                        <td>—</td>

                        
                        <td>
                            <select
                                wire:model.defer="row.role_id"
                                wire:keydown.enter="saveRow"
                                wire:blur="saveRow"
                                autofocus
                                class="input w-full"
                            >
                                <option value="">— выбери —</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($r->role_id); ?>"><?php echo e($r->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                        </td>

                        
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.name"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.sename"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.patronymic"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        
                        <td>
                            <input
                                type="date"
                                wire:model.defer="row.birth_date"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.birth_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <input
                                type="email"
                                wire:model.defer="row.email"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.phone"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        
                        <td>
                            <input
                                type="text"
                                wire:model.defer="row.card"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                            />
                        </td>

                        
                        <td>
                            <input
                                type="password"
                                wire:model.defer="passwordNew"
                                wire:keydown.enter="saveRow"
                                class="input w-full"
                                placeholder="Новый пароль"
                            />
                        </td>

                        <td>
                            <button class="btn btn-success" wire:click="saveRow">Создать</button>
                            <button class="btn btn-ghost" wire:click="cancelEdit">Отмена</button>
                        </td>
                    </tr>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $isRow = $editingRowId === $u->user_id; ?>
                    <tr class="<?php echo e($isRow ? 'is-editing' : ''); ?>">
                        <td><?php echo e($u->user_id); ?></td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='role_id'): ?>
                                <select
                                    wire:model.defer="row.role_id"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                >
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($r->role_id); ?>"><?php echo e($r->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                            <?php else: ?>
                                <div class="cell-action"
                                     wire:click="setField(<?php echo e($u->user_id); ?>, 'role_id')">
                                    <?php echo e($isRow
                                        ? (optional($roles->firstWhere('role_id',$row['role_id']))->name)
                                        : ($u->roles->name ?? '—')); ?>

                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='name'): ?>
                                <input
                                    type="text"
                                    wire:model.defer="row.name"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            <?php else: ?>
                                <div class="cell-action" wire:click="setField(<?php echo e($u->user_id); ?>, 'name')">
                                    <?php echo e($isRow ? $row['name'] : $u->name); ?>

                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='sename'): ?>
                                <input
                                    type="text"
                                    wire:model.defer="row.sename"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                            <?php else: ?>
                                <div class="cell-action" wire:click="setField(<?php echo e($u->user_id); ?>, 'sename')">
                                    <?php echo e($isRow ? $row['sename'] : $u->sename); ?>

                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='patronymic'): ?>
                                <input
                                    type="text"
                                    wire:model.defer="row.patronymic"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                            <?php else: ?>
                                <div class="cell-action" wire:click="setField(<?php echo e($u->user_id); ?>, 'patronymic')">
                                    <?php echo e($isRow ? $row['patronymic'] : $u->patronymic); ?>

                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='birth_date'): ?>
                                <input
                                    type="date"
                                    wire:model.defer="row.birth_date"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.birth_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            <?php else: ?>
                                <div class="cell-action" wire:click="setField(<?php echo e($u->user_id); ?>, 'birth_date')">
                                    <?php echo e($isRow ? $row['birth_date'] : optional($u->birth_date)->format('Y-m-d')); ?>

                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='email'): ?>
                                <input
                                    type="email"
                                    wire:model.defer="row.email"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['row.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            <?php else: ?>
                                <div class="cell-action" wire:click="setField(<?php echo e($u->user_id); ?>, 'email')">
                                    <?php echo e($isRow ? $row['email'] : $u->email); ?>

                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='phone'): ?>
                                <input
                                    type="text"
                                    wire:model.defer="row.phone"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                            <?php else: ?>
                                <div class="cell-action" wire:click="setField(<?php echo e($u->user_id); ?>, 'phone')">
                                    <?php echo e($isRow ? $row['phone'] : $u->phone); ?>

                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='card'): ?>
                                <input
                                    type="text"
                                    wire:model.defer="row.card"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                />
                            <?php else: ?>
                                <div class="cell-action" wire:click="setField(<?php echo e($u->user_id); ?>, 'card')">
                                    <?php echo e($isRow ? $row['card'] : $u->card); ?>

                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if($isRow && $editingField==='password'): ?>
                                <input
                                    type="password"
                                    wire:model.defer="passwordNew"
                                    wire:keydown.enter="saveRow"
                                    wire:blur="saveRow"
                                    autofocus
                                    class="input w-full"
                                    placeholder="Новый пароль"
                                />
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['passwordNew'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            <?php else: ?>
                                <div class="cell-action text-blue-500"
                                     wire:click="setField(<?php echo e($u->user_id); ?>, 'password')">
                                    задать
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>

                        
                        <td>
                            <!--[if BLOCK]><![endif]--><?php if(!$isRow): ?>
                                <button
                                    class="btn btn-ghost text-red-600"
                                    wire:click="deleteUser(<?php echo e($u->user_id); ?>)"
                                    onclick="return confirm('Удалить пользователя?')"
                                >
                                    🗑️
                                </button>
                            <?php else: ?>
                                <button class="btn btn-success" wire:click="saveRow">Сохранить</button>
                                <button class="btn btn-ghost" wire:click="cancelEdit">Отмена</button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </tbody>
        </table>
    </div>

    
    <div class="mt-3 pagination-wrap">
        <?php echo e($users->links()); ?>

    </div>
</div>
<?php /**PATH G:\RentHouse\Rent_house\resources\views/livewire/users-page.blade.php ENDPATH**/ ?>
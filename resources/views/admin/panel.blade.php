@extends('layout')

@section('title', 'Админ-панель')

@section('main_content')
<div class="admin-container">
    <!-- Боковая панель -->
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Админ-панель</h2>
            <button class="sidebar-toggle" id="sidebarToggle" type="button">✕</button>
        </div>
        <nav class="table-list">
            <a href="{{ route('admin.chats') }}" 
               class="table-item {{ request()->routeIs('admin.chats*') ? 'active' : '' }}">
                📨 Все чаты
            </a>
            <a href="{{ route('admin.orders') }}" 
               class="table-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                📦 Все заказы
            </a>
            <a href="{{ route('admin.verification') }}" 
               class="table-item {{ request()->routeIs('admin.verification*') ? 'active' : '' }}">
                ✅ Верификация
            </a>
            <a href="{{ route('admin.bans') }}" 
               class="table-item {{ request()->routeIs('admin.bans*') ? 'active' : '' }}">
                🚫 Баны
            </a>
            <div style="padding: 12px 20px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-top: 1px solid #e5e5e5; margin-top: 8px; padding-top: 16px;">
                Таблицы БД
            </div>
            @foreach ($tables as $table)
                <a href="{{ route('admin.panel', ['table' => $table]) }}" 
                   class="table-item {{ $selectedTable === $table ? 'active' : '' }}">
                    {{ $table }}
                </a>
            @endforeach
        </nav>
    </aside>

    <!-- Основной контент -->
    <main class="admin-content" id="mainContent">
        <div class="content-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" type="button">☰</button>
                <h1>Админ-панель</h1>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if ($selectedTable)
            <!-- Форма добавления записи -->
            <div class="admin-card">
                <h2>Добавить запись в «{{ $selectedTable }}»</h2>
                <form method="POST" action="{{ route('admin.panel') }}">
                    @csrf
                    <input type="hidden" name="table" value="{{ $selectedTable }}">

                    <div class="form-grid">
                        @php
                            $blocked = ['id', 'created_at', 'updated_at', 'deleted_at'];
                        @endphp

                        @foreach ($columns as $col)
                            @continue(in_array($col->name, $blocked, true))

                            <div class="form-group">
                                <label for="{{ $col->name }}">
                                    {{ $col->name }}
                                </label>
                                <input
                                    type="text"
                                    id="{{ $col->name }}"
                                    name="{{ $col->name }}"
                                    value="{{ old($col->name) }}"
                                    @if ($col->notnull && $col->dflt_value === null) required @endif
                                    placeholder="Введите значение"
                                >
                                <span class="field-info">
                                    {{ $col->type ?: 'TEXT' }}
                                    @if ($col->notnull) • обязательное @endif
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Добавить запись
                    </button>
                </form>
            </div>

            <!-- Таблица данных -->
            <div class="admin-card">
                <h2>Таблица «{{ $selectedTable }}»</h2>

                <form method="GET" action="{{ route('admin.panel') }}" class="rows-per-page">
                    <input type="hidden" name="table" value="{{ $selectedTable }}">
                    <input type="hidden" name="page" value="1">
                    <label for="per">Строк на странице:</label>
                    <input
                        id="per"
                        name="per"
                        type="number"
                        min="1"
                        max="100"
                        value="{{ $limit }}"
                        onchange="this.form.submit()"
                    >
                </form>

                @if ($rows->isEmpty())
                    <div class="empty-state">
                        <p>Нет данных для отображения.</p>
                    </div>
                @else
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    @foreach(array_keys((array)$rows->first()) as $col)
                                        <th>{{ $col }}</th>
                                    @endforeach
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    @php
                                        $rowArray = (array)$row;
                                        $primaryKey = null;
                                        $primaryKeyValue = null;
                                        
                                        // Определяем primary key
                                        foreach ($columns as $col) {
                                            if ($col->pk == 1) {
                                                $primaryKey = $col->name;
                                                $primaryKeyValue = $rowArray[$col->name] ?? null;
                                                break;
                                            }
                                        }
                                        
                                        // Если не найден, пробуем стандартные варианты
                                        if (!$primaryKey) {
                                            if (isset($rowArray['id'])) {
                                                $primaryKey = 'id';
                                                $primaryKeyValue = $rowArray['id'];
                                            } elseif (isset($rowArray[$selectedTable . '_id'])) {
                                                $primaryKey = $selectedTable . '_id';
                                                $primaryKeyValue = $rowArray[$selectedTable . '_id'];
                                            } elseif (isset($rowArray['user_id'])) {
                                                $primaryKey = 'user_id';
                                                $primaryKeyValue = $rowArray['user_id'];
                                            }
                                        }
                                        
                                        $rowId = 'row-' . ($primaryKeyValue ?? uniqid());
                                    @endphp
                                    <tr id="{{ $rowId }}" data-table="{{ $selectedTable }}" data-primary-key="{{ $primaryKey }}" data-primary-value="{{ $primaryKeyValue }}">
                                        @foreach ($columns as $col)
                                            @php
                                                $colName = $col->name;
                                                $colValue = $rowArray[$colName] ?? null;
                                                $isBlocked = in_array($colName, ['id', 'created_at', 'updated_at', 'deleted_at'], true);
                                            @endphp
                                            <td data-column="{{ $colName }}" 
                                                data-editable="{{ $isBlocked ? 'false' : 'true' }}"
                                                data-type="{{ $col->type ?? 'TEXT' }}"
                                                data-notnull="{{ $col->notnull ? 'true' : 'false' }}">
                                                <span class="cell-value">{{ is_null($colValue) ? '—' : htmlspecialchars($colValue, ENT_QUOTES, 'UTF-8') }}</span>
                                                @if (!$isBlocked)
                                                    <input type="text" 
                                                           class="cell-input" 
                                                           value="{{ is_null($colValue) ? '' : htmlspecialchars($colValue, ENT_QUOTES, 'UTF-8') }}"
                                                           style="display: none; width: 100%; padding: 6px 8px; border: 1px solid #3b82f6; border-radius: 4px; font-size: 14px;"
                                                           data-original-value="{{ is_null($colValue) ? '' : htmlspecialchars($colValue, ENT_QUOTES, 'UTF-8') }}">
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="actions-cell">
                                            @if ($primaryKey && $primaryKeyValue)
                                                <div class="row-actions" style="display: flex; gap: 8px; align-items: center; white-space: nowrap;">
                                                    <button type="button" 
                                                            class="btn btn-edit edit-row-btn" 
                                                            data-row-id="{{ $rowId }}">
                                                        Редактировать
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-success save-row-btn" 
                                                            data-row-id="{{ $rowId }}"
                                                            style="display: none;">
                                                        Сохранить
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-secondary cancel-row-btn" 
                                                            data-row-id="{{ $rowId }}"
                                                            style="display: none;">
                                                        Отмена
                                                    </button>
                                                    <form method="POST" 
                                                          action="{{ route('admin.panel.delete', ['table' => $selectedTable, 'id' => $primaryKeyValue]) }}" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Вы уверены, что хотите удалить эту запись?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Удалить</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    @php
                        $p = $page;
                        $N = $pages;
                        $items = [];

                        if ($N <= 7) {
                            $items = range(1, $N);
                        } elseif ($p <= 4) {
                            $items = [1, 2, 3, 4, 5, 'dots', $N];
                        } elseif ($p >= $N - 3) {
                            $items = [1, 'dots', $N-4, $N-3, $N-2, $N-1, $N];
                        } else {
                            $items = [1, 'dots', $p-1, $p, $p+1, 'dots', $N];
                        }
                    @endphp

                    @if ($pages > 1)
                        <div class="pagination">
                            <a href="?table={{ urlencode($selectedTable) }}&page={{ max(1, $p-1) }}&per={{ $limit }}"
                               class="{{ $p==1 ? 'disabled' : '' }}">&lsaquo;</a>

                            @foreach ($items as $it)
                                @if ($it === 'dots')
                                    <button type="button" class="pag-ellipsis" data-total="{{ $N }}">…</button>
                                @else
                                    <a href="?table={{ urlencode($selectedTable) }}&page={{ $it }}&per={{ $limit }}"
                                       class="{{ $it==$p ? 'active' : '' }}">{{ $it }}</a>
                                @endif
                            @endforeach

                            <a href="?table={{ urlencode($selectedTable) }}&page={{ min($N, $p+1) }}&per={{ $limit }}"
                               class="{{ $p==$N ? 'disabled' : '' }}">&rsaquo;</a>
                        </div>
                    @endif
                @endif
            </div>
        @else
            <div class="admin-card">
                <div class="empty-state">
                    <p>Выберите таблицу из боковой панели для просмотра данных</p>
                </div>
            </div>
        @endif
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const mainContent = document.getElementById('mainContent');

    // Переключение боковой панели (десктоп)
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('closed');
            mainContent.classList.toggle('expanded');
        });
    }

    // Переключение боковой панели (мобильный)
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // Закрытие боковой панели при клике вне её (мобильный)
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !mobileSidebarToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });

    // Обработка клика по "…" в пагинации
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
            params.set('table', '{{ $selectedTable }}');
            params.set('per', String({{ $limit }}));
            params.set('page', String(p));
            window.location.search = params.toString();
        }
    });

    // Inline-редактирование строк таблицы
    document.addEventListener('click', function(e) {
        // Кнопка "Редактировать"
        if (e.target.classList.contains('edit-row-btn')) {
            const rowId = e.target.getAttribute('data-row-id');
            const row = document.getElementById(rowId);
            if (!row) return;
            
            // Делаем строку редактируемой
            row.classList.add('editing');
            const cells = row.querySelectorAll('td[data-editable="true"]');
            
            cells.forEach(cell => {
                const valueSpan = cell.querySelector('.cell-value');
                const input = cell.querySelector('.cell-input');
                
                if (valueSpan && input) {
                    valueSpan.style.display = 'none';
                    input.style.display = 'block';
                    input.focus();
                }
            });
            
            // Показываем кнопки "Сохранить" и "Отмена", скрываем "Редактировать"
            const actionsCell = row.querySelector('.actions-cell');
            if (actionsCell) {
                actionsCell.querySelector('.edit-row-btn').style.display = 'none';
                actionsCell.querySelector('.save-row-btn').style.display = 'inline-block';
                actionsCell.querySelector('.cancel-row-btn').style.display = 'inline-block';
            }
        }
        
        // Кнопка "Сохранить"
        if (e.target.classList.contains('save-row-btn')) {
            const rowId = e.target.getAttribute('data-row-id');
            const row = document.getElementById(rowId);
            if (!row) return;
            
            const table = row.getAttribute('data-table');
            const primaryKey = row.getAttribute('data-primary-key');
            const primaryValue = row.getAttribute('data-primary-value');
            
            // Собираем данные для отправки
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');
            
            const cells = row.querySelectorAll('td[data-editable="true"]');
            let hasChanges = false;
            
            cells.forEach(cell => {
                const columnName = cell.getAttribute('data-column');
                const input = cell.querySelector('.cell-input');
                const originalValue = input ? input.getAttribute('data-original-value') : '';
                const newValue = input ? input.value : '';
                
                if (newValue !== originalValue) {
                    hasChanges = true;
                }
                
                formData.append(columnName, newValue);
            });
            
            if (!hasChanges) {
                // Нет изменений, просто отменяем редактирование
                cancelRowEdit(rowId);
                return;
            }
            
            // Отправляем запрос на обновление
            const updateUrl = '{{ route("admin.panel.update", ["table" => ":table", "id" => ":id"]) }}'
                .replace(':table', encodeURIComponent(table))
                .replace(':id', encodeURIComponent(primaryValue));
                
            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (response.ok || response.redirected) {
                    // Обновляем значения в ячейках
                    cells.forEach(cell => {
                        const valueSpan = cell.querySelector('.cell-value');
                        const input = cell.querySelector('.cell-input');
                        
                        if (valueSpan && input) {
                            const newValue = input.value || '—';
                            valueSpan.textContent = newValue;
                            input.setAttribute('data-original-value', input.value);
                            valueSpan.style.display = '';
                            input.style.display = 'none';
                        }
                    });
                    
                    // Выходим из режима редактирования
                    row.classList.remove('editing');
                    const actionsCell = row.querySelector('.actions-cell');
                    if (actionsCell) {
                        actionsCell.querySelector('.edit-row-btn').style.display = 'inline-block';
                        actionsCell.querySelector('.save-row-btn').style.display = 'none';
                        actionsCell.querySelector('.cancel-row-btn').style.display = 'none';
                    }
                    
                    // Показываем сообщение об успехе
                    showNotification('Запись успешно обновлена', 'success');
                    
                    // Перезагружаем страницу через небольшую задержку
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    return response.text().then(text => {
                        throw new Error(text || 'Ошибка при сохранении');
                    });
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showNotification('Ошибка при сохранении: ' + error.message, 'error');
            });
        }
        
        // Кнопка "Отмена"
        if (e.target.classList.contains('cancel-row-btn')) {
            const rowId = e.target.getAttribute('data-row-id');
            cancelRowEdit(rowId);
        }
    });
    
    // Функция отмены редактирования строки
    function cancelRowEdit(rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;
        
        const cells = row.querySelectorAll('td[data-editable="true"]');
        
        cells.forEach(cell => {
            const valueSpan = cell.querySelector('.cell-value');
            const input = cell.querySelector('.cell-input');
            
            if (valueSpan && input) {
                // Восстанавливаем оригинальное значение
                input.value = input.getAttribute('data-original-value');
                valueSpan.style.display = '';
                input.style.display = 'none';
            }
        });
        
        // Выходим из режима редактирования
        row.classList.remove('editing');
        const actionsCell = row.querySelector('.actions-cell');
        if (actionsCell) {
            actionsCell.querySelector('.edit-row-btn').style.display = 'inline-block';
            actionsCell.querySelector('.save-row-btn').style.display = 'none';
            actionsCell.querySelector('.cancel-row-btn').style.display = 'none';
        }
    }
    
    // Функция показа уведомлений
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'error'}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '10000';
        notification.style.minWidth = '300px';
        notification.style.padding = '16px 20px';
        notification.style.borderRadius = '8px';
        notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
});
</script>
@endsection


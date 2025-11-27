<div class="house-calendar-section" style="background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
    <h2 style="font-size: 14px; font-weight: 600; color: #1c1c1c; margin-bottom: 8px; margin-top: 0;">Календарь занятости</h2>
    <div class="house-calendar-container" data-house-id="{{ $house->house_id }}" data-dates='@json($house->house_calendar->dates ?? [])'>
        <div class="calendar-wrapper">
            <div class="calendar-header">
                <button class="calendar-nav-btn" data-action="prev">‹</button>
                <div class="calendar-month-year"></div>
                <button class="calendar-nav-btn" data-action="next">›</button>
            </div>
            <div class="calendar-grid">
                <div class="calendar-weekdays">
                    <div>Пн</div>
                    <div>Вт</div>
                    <div>Ср</div>
                    <div>Чт</div>
                    <div>Пт</div>
                    <div>Сб</div>
                    <div>Вс</div>
                </div>
                <div class="calendar-days"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .house-calendar-container {
        margin-top: 0;
    }

    .calendar-wrapper {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px;
    }

    .calendar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e5e7eb;
    }

    .calendar-month-year {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
        flex: 1;
        text-align: center;
    }

    .calendar-nav-btn {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        color: #374151;
        transition: all 0.2s;
        padding: 0;
    }

    .calendar-nav-btn:hover {
        background: #e5e7eb;
        border-color: #d1d5db;
    }

    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        margin-bottom: 4px;
    }

    .calendar-weekdays > div {
        text-align: center;
        font-size: 10px;
        font-weight: 600;
        color: #6b7280;
        padding: 2px;
    }

    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #111827;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        min-height: 24px;
    }

    .calendar-day:not(.past-date):not(.other-month):hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .calendar-day.past-date {
        cursor: not-allowed;
    }

    .calendar-day.other-month {
        cursor: default;
    }

    .calendar-day.loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .calendar-day.other-month {
        color: #d1d5db;
        background: #f9fafb;
    }

    .calendar-day.today {
        background: #eff6ff;
        border-color: #3b82f6;
        font-weight: 600;
        color: #1e40af;
    }

    .calendar-day.booked {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
        font-weight: 600;
    }

    .calendar-day.booked:hover {
        background: #fecaca;
    }


    .calendar-day.past-date.booked {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
        opacity: 0.7;
    }

    .calendar-day.range-start {
        background: #dbeafe;
        border-color: #60a5fa;
        border-left-width: 1.5px;
    }

    .calendar-day.range-end {
        background: #dbeafe;
        border-color: #60a5fa;
        border-right-width: 1.5px;
    }

    .calendar-day.range-middle {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    .calendar-day.range-selected {
        background: #bfdbfe;
        border-color: #3b82f6;
    }

    .calendar-day.range-removing {
        background: #fca5a5;
        border-color: #ef4444;
        color: #991b1b;
        font-weight: 600;
    }

    .calendar-day:not(.past-date):not(.other-month):not(.booked) {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
</style>

<script>
(function() {
    // Функция инициализации календарей
    function initCalendars() {
        const calendarContainers = document.querySelectorAll('.house-calendar-container:not([data-initialized])');
        
        calendarContainers.forEach(container => {
            // Помечаем контейнер как инициализированный
            container.setAttribute('data-initialized', 'true');
            
            const houseId = container.dataset.houseId;
            const datesData = container.dataset.dates;
            let bookedDates = [];
            
            try {
                bookedDates = datesData ? JSON.parse(datesData) : [];
                // Преобразуем даты в формат YYYY-MM-DD для сравнения
                bookedDates = bookedDates.map(date => {
                    if (typeof date === 'string') {
                        return date.split('T')[0]; // Убираем время, если есть
                    }
                    return date;
                });
            } catch (e) {
                console.warn('Ошибка парсинга дат календаря:', e);
            }

            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();

            // Состояние выбора периода
            let rangeStart = null;
            let rangeEnd = null;
            let isRangeSelecting = false;
            
            // Переменные для drag-and-drop выбора
            let isDragging = false;
            let dragStartDate = null;
            let draggedDates = new Set(); // Множество дат, через которые прошли при перетаскивании
            let isDragRemoving = false; // Режим удаления при перетаскивании
            let wasDragging = false;

            // Функция для получения всех дат в периоде
            function getDatesInRange(startDate, endDate) {
                const dates = [];
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                // Убеждаемся, что start <= end
                if (start > end) {
                    [start, end] = [end, start];
                }
                
                const current = new Date(start);
                while (current <= end) {
                    dates.push(current.toISOString().split('T')[0]);
                    current.setDate(current.getDate() + 1);
                }
                
                return dates;
            }

            // Функция для обновления визуализации выбранного периода
            function updateRangeVisualization() {
                const allDays = container.querySelectorAll('.calendar-day:not(.other-month)');
                allDays.forEach(dayEl => {
                    dayEl.classList.remove('range-start', 'range-end', 'range-middle', 'range-selected');
                });

                if (rangeStart) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (rangeEnd) {
                        // Есть и начало, и конец - определяем реальное начало и конец периода
                        const startDate = new Date(rangeStart);
                        const endDate = new Date(rangeEnd);
                        
                        // Определяем реальное начало (минимальная дата) и конец (максимальная дата)
                        const actualStart = startDate <= endDate ? rangeStart : rangeEnd;
                        const actualEnd = startDate <= endDate ? rangeEnd : rangeStart;
                        
                        const rangeDates = getDatesInRange(actualStart, actualEnd);
                        
                        allDays.forEach(dayEl => {
                            const dateStr = dayEl.dataset.date;
                            if (!dateStr) return;
                            
                            const dayDate = new Date(dateStr);
                            dayDate.setHours(0, 0, 0, 0);
                            
                            if (rangeDates.includes(dateStr) && dayDate >= today) {
                                if (dateStr === actualStart) {
                                    dayEl.classList.add('range-start');
                                } else if (dateStr === actualEnd) {
                                    dayEl.classList.add('range-end');
                                } else {
                                    dayEl.classList.add('range-middle');
                                }
                            }
                        });
                    } else {
                        // Только начало - показываем только начальную дату
                        allDays.forEach(dayEl => {
                            const dateStr = dayEl.dataset.date;
                            if (dateStr === rangeStart) {
                                const dayDate = new Date(dateStr);
                                dayDate.setHours(0, 0, 0, 0);
                                if (dayDate >= today) {
                                    dayEl.classList.add('range-start');
                                }
                            }
                        });
                    }
                }
            }

            // Функция для блокировки периода
            async function toggleDateRange(startDate, endDate, action, specificDates = null) {
                // Если переданы конкретные даты - используем их, иначе генерируем промежуток
                let rangeDates;
                if (specificDates && Array.isArray(specificDates)) {
                    rangeDates = specificDates;
                } else {
                    rangeDates = getDatesInRange(startDate, endDate);
                }
                
                // Фильтруем только будущие даты
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const validDates = rangeDates.filter(dateStr => {
                    const date = new Date(dateStr);
                    date.setHours(0, 0, 0, 0);
                    return date >= today;
                });

                if (validDates.length === 0) {
                    alert('Выбранный период содержит только прошедшие даты');
                    return;
                }

                try {
                    // Получаем CSRF токен
                    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!csrfToken) {
                        const tokenInput = document.querySelector('input[name="_token"]');
                        csrfToken = tokenInput ? tokenInput.value : '{{ csrf_token() }}';
                    }

                    const response = await fetch(`/house/${houseId}/calendar/dates/range`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            dates: validDates,
                            action: action
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Обновляем локальный список дат
                        bookedDates = data.dates.map(date => {
                            if (typeof date === 'string') {
                                return date.split('T')[0];
                            }
                            return date;
                        });
                        
                        // Обновляем data-атрибут контейнера
                        container.dataset.dates = JSON.stringify(bookedDates);
                        
                        // Сбрасываем выбор периода
                        rangeStart = null;
                        rangeEnd = null;
                        isRangeSelecting = false;
                        
                        // Перерисовываем календарь
                        renderCalendar();
                    } else {
                        throw new Error(data.error || 'Ошибка обновления периода');
                    }
                } catch (error) {
                    console.error('Ошибка при обновлении периода:', error);
                    alert('Не удалось обновить период: ' + error.message);
                }
            }

            // Функция для обновления даты на сервере
            async function toggleDate(dateStr, dayEl) {
                const isBooked = bookedDates.includes(dateStr);
                const action = isBooked ? 'remove' : 'add';
                
                // Визуальная обратная связь
                dayEl.classList.add('loading');
                
                try {
                    // Получаем CSRF токен
                    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!csrfToken) {
                        const tokenInput = document.querySelector('input[name="_token"]');
                        csrfToken = tokenInput ? tokenInput.value : '{{ csrf_token() }}';
                    }

                    const response = await fetch(`/house/${houseId}/calendar/dates`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            date: dateStr,
                            action: action
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Обновляем локальный список дат
                        bookedDates = data.dates.map(date => {
                            if (typeof date === 'string') {
                                return date.split('T')[0];
                            }
                            return date;
                        });
                        
                        // Обновляем визуальное состояние
                        if (action === 'add') {
                            dayEl.classList.add('booked');
                            dayEl.title = 'Занято (нажмите, чтобы разблокировать)';
                        } else {
                            dayEl.classList.remove('booked');
                            dayEl.title = '';
                        }
                        
                        // Обновляем data-атрибут контейнера
                        container.dataset.dates = JSON.stringify(bookedDates);
                    } else {
                        throw new Error(data.error || 'Ошибка обновления даты');
                    }
                } catch (error) {
                    console.error('Ошибка при обновлении даты:', error);
                    alert('Не удалось обновить дату: ' + error.message);
                } finally {
                    dayEl.classList.remove('loading');
                }
            }

        function renderCalendar() {
            const monthYearEl = container.querySelector('.calendar-month-year');
            const daysEl = container.querySelector('.calendar-days');
            
            const monthNames = [
                'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
            ];

            monthYearEl.textContent = `${monthNames[currentMonth]} ${currentYear}`;

            const firstDay = new Date(currentYear, currentMonth, 1);
            const lastDay = new Date(currentYear, currentMonth + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = (firstDay.getDay() + 6) % 7; // Понедельник = 0

            daysEl.innerHTML = '';

            // Дни предыдущего месяца
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Обнуляем время для корректного сравнения
            const prevMonthLastDay = new Date(currentYear, currentMonth, 0).getDate();
            for (let i = startingDayOfWeek - 1; i >= 0; i--) {
                const day = prevMonthLastDay - i;
                const dayEl = document.createElement('div');
                dayEl.className = 'calendar-day other-month';
                
                // Проверяем, является ли день прошедшим
                const prevMonth = currentMonth === 0 ? 11 : currentMonth - 1;
                const prevYear = currentMonth === 0 ? currentYear - 1 : currentYear;
                const prevDate = new Date(prevYear, prevMonth, day);
                prevDate.setHours(0, 0, 0, 0);
                
                if (prevDate < today) {
                    dayEl.classList.add('past-date');
                }
                
                dayEl.textContent = day;
                daysEl.appendChild(dayEl);
            }

            // Дни текущего месяца
            for (let day = 1; day <= daysInMonth; day++) {
                const dayEl = document.createElement('div');
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const currentDayDate = new Date(currentYear, currentMonth, day);
                currentDayDate.setHours(0, 0, 0, 0);
                
                dayEl.className = 'calendar-day';
                dayEl.textContent = day;

                // Проверяем, является ли день прошедшим (до сегодняшней даты)
                if (currentDayDate < today) {
                    dayEl.classList.add('past-date');
                }

                // Проверяем, является ли день сегодняшним
                if (currentYear === today.getFullYear() && 
                    currentMonth === today.getMonth() && 
                    day === today.getDate()) {
                    dayEl.classList.add('today');
                }

                // Сохраняем дату в элементе для удобства
                dayEl.dataset.date = dateStr;

                // Проверяем, занят ли день (но не добавляем класс booked если идет перетаскивание)
                if (bookedDates.includes(dateStr)) {
                    if (!(isDragging && draggedDates && draggedDates.has(dateStr))) {
                        dayEl.classList.add('booked');
                    }
                    dayEl.title = 'Занято (нажмите, чтобы разблокировать; Ctrl+клик для выбора периода; зажмите ЛКМ для удаления)';
                } else if (currentDayDate >= today) {
                    dayEl.title = 'Нажмите, чтобы заблокировать; Ctrl+клик для выбора периода; зажмите ЛКМ для выбора';
                }
                
                // Визуализация перетаскивания (после добавления всех классов, но с приоритетом)
                if (isDragging && draggedDates && draggedDates.has(dateStr)) {
                    // Убираем класс booked для показа предпросмотра
                    dayEl.classList.remove('booked');
                    
                    if (isDragRemoving) {
                        // Режим удаления - красная подсветка
                        dayEl.classList.add('range-removing');
                    } else {
                        // Режим добавления - синяя подсветка
                        const datesArray = Array.from(draggedDates).sort();
                        if (datesArray.length > 0) {
                            if (dateStr === datesArray[0]) {
                                dayEl.classList.add('range-start');
                            } else if (dateStr === datesArray[datesArray.length - 1]) {
                                dayEl.classList.add('range-end');
                            } else {
                                dayEl.classList.add('range-middle');
                            }
                        }
                    }
                }

                // Добавляем обработчик клика только для будущих дат
                if (currentDayDate >= today && !dayEl.classList.contains('other-month')) {
                    // Обработчик mousedown - начало перетаскивания
                    dayEl.addEventListener('mousedown', function(e) {
                        if (e.button === 0) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            isDragging = true;
                            dragStartDate = dateStr;
                            
                            // Определяем режим: если начинаем с заблокированной даты - режим удаления
                            isDragRemoving = bookedDates.includes(dateStr);
                            
                            // Инициализируем множество пройденных дат
                            draggedDates = new Set();
                            draggedDates.add(dateStr);
                            
                            // Меняем курсор
                            document.body.style.cursor = isDragRemoving ? 'not-allowed' : 'grabbing';
                            
                            // Добавляем обработчики для всего документа
                            document.addEventListener('mousemove', handleMouseMove);
                            document.addEventListener('mouseup', handleMouseUp);
                        }
                    });
                    
                    // Обработчик mouseenter для отслеживания пройденных дат
                    dayEl.addEventListener('mouseenter', function() {
                        if (isDragging && currentDayDate >= today) {
                            const wasAdded = draggedDates.has(dateStr);
                            if (!wasAdded) {
                                draggedDates.add(dateStr);
                                // Обновляем визуализацию без полной перерисовки
                                updateDragVisualization();
                            }
                        }
                    });
                    
                    dayEl.addEventListener('click', function(e) {
                        // Если было перетаскивание, не обрабатываем клик
                        if (wasDragging) {
                            wasDragging = false;
                            return;
                        }
                        
                        const isCtrlPressed = e.ctrlKey || e.metaKey; // Поддержка Cmd на Mac
                        
                        if (isCtrlPressed) {
                            // Режим выбора периода
                            if (!rangeStart) {
                                // Первый клик - устанавливаем начальную дату
                                rangeStart = dateStr;
                                rangeEnd = null;
                                isRangeSelecting = true;
                                updateRangeVisualization();
                            } else if (rangeStart === dateStr) {
                                // Клик по той же дате - сброс выбора
                                rangeStart = null;
                                rangeEnd = null;
                                isRangeSelecting = false;
                                updateRangeVisualization();
                            } else {
                                // Второй клик - устанавливаем конечную дату и блокируем период
                                rangeEnd = dateStr;
                                isRangeSelecting = false;
                                
                                // Определяем реальное начало и конец периода (независимо от порядка кликов)
                                const startDate = new Date(rangeStart);
                                const endDate = new Date(rangeEnd);
                                const actualStart = startDate <= endDate ? rangeStart : rangeEnd;
                                const actualEnd = startDate <= endDate ? rangeEnd : rangeStart;
                                
                                // Определяем действие: если все даты в периоде заблокированы - разблокируем, иначе блокируем
                                const rangeDates = getDatesInRange(actualStart, actualEnd);
                                const today = new Date();
                                today.setHours(0, 0, 0, 0);
                                const validDates = rangeDates.filter(d => {
                                    const date = new Date(d);
                                    date.setHours(0, 0, 0, 0);
                                    return date >= today;
                                });
                                
                                const allBooked = validDates.every(d => bookedDates.includes(d));
                                const action = allBooked ? 'remove' : 'add';
                                
                                // Передаем даты в правильном порядке (от меньшей к большей)
                                toggleDateRange(actualStart, actualEnd, action);
                            }
                        } else {
                            // Обычный клик - блокировка/разблокировка одной даты
                            if (isRangeSelecting) {
                                // Сбрасываем выбор периода при обычном клике
                                rangeStart = null;
                                rangeEnd = null;
                                isRangeSelecting = false;
                                updateRangeVisualization();
                            }
                            toggleDate(dateStr, dayEl);
                        }
                    });
                }

                daysEl.appendChild(dayEl);
            }

            // Дни следующего месяца
            const totalCells = startingDayOfWeek + daysInMonth;
            const remainingCells = 42 - totalCells; // 6 недель * 7 дней
            for (let day = 1; day <= remainingCells && day <= 14; day++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'calendar-day other-month';
                
                // Проверяем, является ли день прошедшим (маловероятно, но на всякий случай)
                const nextMonth = currentMonth === 11 ? 0 : currentMonth + 1;
                const nextYear = currentMonth === 11 ? currentYear + 1 : currentYear;
                const nextDate = new Date(nextYear, nextMonth, day);
                nextDate.setHours(0, 0, 0, 0);
                
                if (nextDate < today) {
                    dayEl.classList.add('past-date');
                }
                
                dayEl.textContent = day;
                daysEl.appendChild(dayEl);
            }
            
            // Обновляем визуализацию выбранного периода
            updateRangeVisualization();
            
            // Обновляем визуализацию перетаскивания после рендеринга
            if (isDragging && draggedDates && draggedDates.size > 0) {
                updateDragVisualization();
            }
        }
        
        // Функция для обновления визуализации перетаскивания
        function updateDragVisualization() {
            const allDays = container.querySelectorAll('.calendar-day[data-date]');
            allDays.forEach(dayEl => {
                const dateStr = dayEl.dataset.date;
                if (!dateStr) return;
                
                // Убираем предыдущие классы перетаскивания
                dayEl.classList.remove('range-start', 'range-end', 'range-middle', 'range-removing');
                
                if (draggedDates.has(dateStr)) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const date = new Date(dateStr);
                    date.setHours(0, 0, 0, 0);
                    
                    if (date >= today) {
                        // Убираем класс booked для показа предпросмотра
                        dayEl.classList.remove('booked');
                        
                        if (isDragRemoving) {
                            // Режим удаления - красная подсветка
                            dayEl.classList.add('range-removing');
                        } else {
                            // Режим добавления - синяя подсветка
                            const datesArray = Array.from(draggedDates).sort();
                            if (datesArray.length > 0) {
                                if (dateStr === datesArray[0]) {
                                    dayEl.classList.add('range-start');
                                } else if (dateStr === datesArray[datesArray.length - 1]) {
                                    dayEl.classList.add('range-end');
                                } else {
                                    dayEl.classList.add('range-middle');
                                }
                            }
                        }
                    }
                } else {
                    // Восстанавливаем класс booked если нужно
                    if (bookedDates.includes(dateStr)) {
                        dayEl.classList.add('booked');
                    }
                }
            });
        }

        // Обработчик движения мыши при перетаскивании
        function handleMouseMove(e) {
            if (!isDragging) return;
            
            // Находим элемент под курсором
            const elementUnderMouse = document.elementFromPoint(e.clientX, e.clientY);
            if (!elementUnderMouse) return;
            
            // Ищем родительский элемент с data-date
            let dayElement = elementUnderMouse;
            while (dayElement && !dayElement.dataset.date) {
                dayElement = dayElement.parentElement;
            }
            
            if (dayElement && dayElement.dataset.date) {
                const newDate = dayElement.dataset.date;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const date = new Date(newDate);
                date.setHours(0, 0, 0, 0);
                
                // Добавляем дату в множество пройденных, если она валидна
                if (date >= today) {
                    const wasAdded = draggedDates.has(newDate);
                    if (!wasAdded) {
                        draggedDates.add(newDate);
                        // Обновляем визуализацию без полной перерисовки
                        updateDragVisualization();
                    }
                }
            }
        }
        
        // Обработчик отпускания кнопки мыши
        function handleMouseUp(e) {
            if (!isDragging) return;
            
            wasDragging = true; // Флаг для предотвращения обработки клика
            
            // Восстанавливаем курсор
            document.body.style.cursor = '';
            
            // Удаляем обработчики
            document.removeEventListener('mousemove', handleMouseMove);
            document.removeEventListener('mouseup', handleMouseUp);
            
            // Обрабатываем пройденные даты
            if (draggedDates.size > 0) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Фильтруем только валидные даты
                const validDates = Array.from(draggedDates).filter(dateStr => {
                    const date = new Date(dateStr);
                    date.setHours(0, 0, 0, 0);
                    return date >= today;
                });
                
                if (validDates.length > 0) {
                    // Сортируем даты
                    validDates.sort();
                    
                    // Определяем действие
                    const allBooked = validDates.every(d => bookedDates.includes(d));
                    const action = allBooked ? 'remove' : 'add';
                    
                    // Определяем начало и конец для визуализации
                    const actualStart = validDates[0];
                    const actualEnd = validDates[validDates.length - 1];
                    
                    // Блокируем/разблокируем только те даты, через которые прошли
                    toggleDateRange(actualStart, actualEnd, action, validDates);
                }
            } else if (dragStartDate) {
                // Если кликнули на одну дату - просто переключаем её
                toggleDate(dragStartDate, container.querySelector(`[data-date="${dragStartDate}"]`));
            }
            
            // Сбрасываем состояние перетаскивания
            isDragging = false;
            isDragRemoving = false;
            dragStartDate = null;
            draggedDates = new Set();
            
            // Сбрасываем флаг через небольшую задержку
            setTimeout(() => {
                wasDragging = false;
            }, 100);
        }

        // Обработчики навигации
        container.querySelectorAll('.calendar-nav-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.dataset.action === 'prev') {
                    currentMonth--;
                    if (currentMonth < 0) {
                        currentMonth = 11;
                        currentYear--;
                    }
                } else {
                    currentMonth++;
                    if (currentMonth > 11) {
                        currentMonth = 0;
                        currentYear++;
                    }
                }
                // Сбрасываем выбор периода при смене месяца
                rangeStart = null;
                rangeEnd = null;
                isRangeSelecting = false;
                isDragging = false;
                draggedDates = new Set();
                renderCalendar();
            });
        });
        
        // Сбрасываем перетаскивание при выходе за пределы календаря
        container.addEventListener('mouseleave', function() {
            if (isDragging) {
                const event = new MouseEvent('mouseup', { bubbles: true, cancelable: true });
                document.dispatchEvent(event);
            }
        });

        // Обработчик для сброса выбора периода при клике вне календаря
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target) && isRangeSelecting) {
                rangeStart = null;
                rangeEnd = null;
                isRangeSelecting = false;
                updateRangeVisualization();
            }
        });

        // Первоначальная отрисовка
        renderCalendar();
        });
    }

    // Инициализация при загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendars);
    } else {
        // Если DOM уже загружен (AJAX-загрузка)
        initCalendars();
    }

    // Экспортируем функцию для вызова извне (для AJAX-загрузки)
    window.initHouseCalendars = initCalendars;
})();
</script>


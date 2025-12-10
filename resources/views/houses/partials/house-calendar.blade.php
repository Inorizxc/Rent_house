<div class="house-calendar-section">
    <h2 class="house-calendar-section-title">Календарь занятости</h2>
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

<script>
(function() {
    function initCalendars() {
        const calendarContainers = document.querySelectorAll('.house-calendar-container:not([data-initialized])');
        
        calendarContainers.forEach(container => {
            container.setAttribute('data-initialized', 'true');
            
            const houseId = container.dataset.houseId;
            const datesData = container.dataset.dates;
            let bookedDates = [];
            
            try {
                bookedDates = datesData ? JSON.parse(datesData) : [];
                bookedDates = bookedDates.map(date => {
                    if (typeof date === 'string') {
                        return date.split('T')[0];
                    }
                    return date;
                });
            } catch (e) {
                console.warn('Ошибка парсинга дат календаря:', e);
            }

            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();

            let rangeStart = null;
            let rangeEnd = null;
            let isRangeSelecting = false;
            
            let isDragging = false;
            let dragStartDate = null;
            let draggedDates = new Set();
            let isDragRemoving = false;
            let wasDragging = false;

            function getDatesInRange(startDate, endDate) {
                const dates = [];
                const start = new Date(startDate);
                const end = new Date(endDate);
                
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

            function updateRangeVisualization() {
                const allDays = container.querySelectorAll('.calendar-day:not(.other-month)');
                allDays.forEach(dayEl => {
                    dayEl.classList.remove('range-start', 'range-end', 'range-middle', 'range-selected');
                });

                if (rangeStart) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (rangeEnd) {
                        const startDate = new Date(rangeStart);
                        const endDate = new Date(rangeEnd);
                        
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

            async function toggleDateRange(startDate, endDate, action, specificDates = null) {
                let rangeDates;
                if (specificDates && Array.isArray(specificDates)) {
                    rangeDates = specificDates;
                } else {
                    rangeDates = getDatesInRange(startDate, endDate);
                }
                
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
                        bookedDates = data.dates.map(date => {
                            if (typeof date === 'string') {
                                return date.split('T')[0];
                            }
                            return date;
                        });
                        
                        container.dataset.dates = JSON.stringify(bookedDates);
                        
                        rangeStart = null;
                        rangeEnd = null;
                        isRangeSelecting = false;
                        
                        renderCalendar();
                    } else {
                        throw new Error(data.error || 'Ошибка обновления периода');
                    }
                } catch (error) {
                    console.error('Ошибка при обновлении периода:', error);
                    alert('Не удалось обновить период: ' + error.message);
                }
            }

            async function toggleDate(dateStr, dayEl) {
                const isBooked = bookedDates.includes(dateStr);
                const action = isBooked ? 'remove' : 'add';
                
                dayEl.classList.add('loading');
                
                try {
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
                        bookedDates = data.dates.map(date => {
                            if (typeof date === 'string') {
                                return date.split('T')[0];
                            }
                            return date;
                        });
                        
                        if (action === 'add') {
                            dayEl.classList.add('booked');
                            dayEl.title = 'Занято (нажмите, чтобы разблокировать)';
                        } else {
                            dayEl.classList.remove('booked');
                            dayEl.title = '';
                        }
                        
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

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const prevMonthLastDay = new Date(currentYear, currentMonth, 0).getDate();
            for (let i = startingDayOfWeek - 1; i >= 0; i--) {
                const day = prevMonthLastDay - i;
                const dayEl = document.createElement('div');
                dayEl.className = 'calendar-day other-month';
                
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

            for (let day = 1; day <= daysInMonth; day++) {
                const dayEl = document.createElement('div');
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const currentDayDate = new Date(currentYear, currentMonth, day);
                currentDayDate.setHours(0, 0, 0, 0);
                
                dayEl.className = 'calendar-day';
                dayEl.textContent = day;

                if (currentDayDate < today) {
                    dayEl.classList.add('past-date');
                }

                if (currentYear === today.getFullYear() && 
                    currentMonth === today.getMonth() && 
                    day === today.getDate()) {
                    dayEl.classList.add('today');
                }

                dayEl.dataset.date = dateStr;

                if (bookedDates.includes(dateStr)) {
                    if (!(isDragging && draggedDates && draggedDates.has(dateStr))) {
                        dayEl.classList.add('booked');
                    }
                    dayEl.title = 'Занято (нажмите, чтобы разблокировать; Ctrl+клик для выбора периода; зажмите ЛКМ для удаления)';
                } else if (currentDayDate >= today) {
                    dayEl.title = 'Нажмите, чтобы заблокировать; Ctrl+клик для выбора периода; зажмите ЛКМ для выбора';
                }
                
                if (isDragging && draggedDates && draggedDates.has(dateStr)) {
                    dayEl.classList.remove('booked');
                    
                    if (isDragRemoving) {
                        dayEl.classList.add('range-removing');
                    } else {
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

                if (currentDayDate >= today && !dayEl.classList.contains('other-month')) {
                    dayEl.addEventListener('mousedown', function(e) {
                        if (e.button === 0) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            isDragging = true;
                            dragStartDate = dateStr;
                            
                            isDragRemoving = bookedDates.includes(dateStr);
                            
                            draggedDates = new Set();
                            draggedDates.add(dateStr);
                            
                            document.body.style.cursor = isDragRemoving ? 'not-allowed' : 'grabbing';
                            
                            document.addEventListener('mousemove', handleMouseMove);
                            document.addEventListener('mouseup', handleMouseUp);
                        }
                    });
                    
                    dayEl.addEventListener('mouseenter', function() {
                        if (isDragging && currentDayDate >= today) {
                            const wasAdded = draggedDates.has(dateStr);
                            if (!wasAdded) {
                                draggedDates.add(dateStr);
                                updateDragVisualization();
                            }
                        }
                    });
                    
                    dayEl.addEventListener('click', function(e) {
                        if (wasDragging) {
                            wasDragging = false;
                            return;
                        }
                        
                        const isCtrlPressed = e.ctrlKey || e.metaKey;
                        
                        if (isCtrlPressed) {
                            if (!rangeStart) {
                                rangeStart = dateStr;
                                rangeEnd = null;
                                isRangeSelecting = true;
                                updateRangeVisualization();
                            } else if (rangeStart === dateStr) {
                                rangeStart = null;
                                rangeEnd = null;
                                isRangeSelecting = false;
                                updateRangeVisualization();
                            } else {
                                rangeEnd = dateStr;
                                isRangeSelecting = false;
                                
                                const startDate = new Date(rangeStart);
                                const endDate = new Date(rangeEnd);
                                const actualStart = startDate <= endDate ? rangeStart : rangeEnd;
                                const actualEnd = startDate <= endDate ? rangeEnd : rangeStart;
                                
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
                                
                                toggleDateRange(actualStart, actualEnd, action);
                            }
                        } else {
                            if (isRangeSelecting) {
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

            const totalCells = startingDayOfWeek + daysInMonth;
            const remainingCells = 42 - totalCells; // 6 недель * 7 дней
            for (let day = 1; day <= remainingCells && day <= 14; day++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'calendar-day other-month';
                
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
            
            updateRangeVisualization();
            
            if (isDragging && draggedDates && draggedDates.size > 0) {
                updateDragVisualization();
            }
        }
        
        function updateDragVisualization() {
            const allDays = container.querySelectorAll('.calendar-day[data-date]');
            allDays.forEach(dayEl => {
                const dateStr = dayEl.dataset.date;
                if (!dateStr) return;
                
                dayEl.classList.remove('range-start', 'range-end', 'range-middle', 'range-removing');
                
                if (draggedDates.has(dateStr)) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const date = new Date(dateStr);
                    date.setHours(0, 0, 0, 0);
                    
                    if (date >= today) {
                        dayEl.classList.remove('booked');
                        
                        if (isDragRemoving) {
                            dayEl.classList.add('range-removing');
                        } else {
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
                    if (bookedDates.includes(dateStr)) {
                        dayEl.classList.add('booked');
                    }
                }
            });
        }

        function handleMouseMove(e) {
            if (!isDragging) return;
            
            const elementUnderMouse = document.elementFromPoint(e.clientX, e.clientY);
            if (!elementUnderMouse) return;
            
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
                
                if (date >= today) {
                    const wasAdded = draggedDates.has(newDate);
                    if (!wasAdded) {
                        draggedDates.add(newDate);
                        updateDragVisualization();
                    }
                }
            }
        }
        

        function handleMouseUp(e) {
            if (!isDragging) return;
            
            wasDragging = true; 
            document.body.style.cursor = '';
            
            document.removeEventListener('mousemove', handleMouseMove);
            document.removeEventListener('mouseup', handleMouseUp);
            
            if (draggedDates.size > 0) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                const validDates = Array.from(draggedDates).filter(dateStr => {
                    const date = new Date(dateStr);
                    date.setHours(0, 0, 0, 0);
                    return date >= today;
                });
                
                if (validDates.length > 0) {
                    validDates.sort();
                    
                    const allBooked = validDates.every(d => bookedDates.includes(d));
                    const action = allBooked ? 'remove' : 'add';
                    
                    const actualStart = validDates[0];
                    const actualEnd = validDates[validDates.length - 1];
                    
                    toggleDateRange(actualStart, actualEnd, action, validDates);
                }
            } else if (dragStartDate) {
                toggleDate(dragStartDate, container.querySelector(`[data-date="${dragStartDate}"]`));
            }
            
            isDragging = false;
            isDragRemoving = false;
            dragStartDate = null;
            draggedDates = new Set();
            
            setTimeout(() => {
                wasDragging = false;
            }, 100);
        }

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
                rangeStart = null;
                rangeEnd = null;
                isRangeSelecting = false;
                isDragging = false;
                draggedDates = new Set();
                renderCalendar();
            });
        });
        
        container.addEventListener('mouseleave', function() {
            if (isDragging) {
                const event = new MouseEvent('mouseup', { bubbles: true, cancelable: true });
                document.dispatchEvent(event);
            }
        });

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target) && isRangeSelecting) {
                rangeStart = null;
                rangeEnd = null;
                isRangeSelecting = false;
                updateRangeVisualization();
            }
        });

        renderCalendar();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendars);
    } else {
        // Если DOM уже загружен (AJAX-загрузка)
        initCalendars();
    }

    window.initHouseCalendars = initCalendars;
})();
</script>


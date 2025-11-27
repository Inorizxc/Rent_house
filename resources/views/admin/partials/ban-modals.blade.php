<!-- Модальное окно для бана -->
<div class="ban-modal" id="banModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="banModalTitle">Забанить</h3>
        </div>
        <form method="POST" id="banForm" action="">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Тип бана:</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="ban-temporary" name="ban_type" value="temporary" checked onchange="toggleBanDate()">
                            <label for="ban-temporary">Временный</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="ban-permanent" name="ban_type" value="permanent" onchange="toggleBanDate()">
                            <label for="ban-permanent">Навсегда</label>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="ban-date-group">
                    <label for="ban_until">Дата окончания бана:</label>
                    <input type="datetime-local" id="ban_until" name="ban_until" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeBanModal()">Отмена</button>
                <button type="submit" class="btn btn-danger">Забанить</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBanModal(type, id, name) {
    const modal = document.getElementById('banModal');
    const form = document.getElementById('banForm');
    const title = document.getElementById('banModalTitle');
    const dateInput = document.getElementById('ban_until');
    
    if (type === 'user') {
        form.action = '{{ route("admin.bans.user.ban", ":id") }}'.replace(':id', id);
        title.textContent = 'Забанить пользователя: ' + name;
    } else {
        form.action = '{{ route("admin.bans.house.ban", ":id") }}'.replace(':id', id);
        title.textContent = 'Забанить дом: ' + name;
    }
    
    // Устанавливаем минимальную дату (сегодня) и значение по умолчанию (через 7 дней)
    const now = new Date();
    now.setDate(now.getDate() + 7);
    const defaultDate = now.toISOString().slice(0, 16);
    dateInput.min = new Date().toISOString().slice(0, 16);
    dateInput.value = defaultDate;
    
    modal.classList.add('show');
    toggleBanDate();
}

function closeBanModal() {
    const modal = document.getElementById('banModal');
    modal.classList.remove('show');
}

function toggleBanDate() {
    const temporaryRadio = document.getElementById('ban-temporary');
    const dateGroup = document.getElementById('ban-date-group');
    const dateInput = document.getElementById('ban_until');
    
    if (temporaryRadio.checked) {
        dateGroup.style.display = 'block';
        dateInput.required = true;
    } else {
        dateGroup.style.display = 'none';
        dateInput.required = false;
    }
}

// Закрытие модального окна при клике вне его
document.addEventListener('click', function(e) {
    const modal = document.getElementById('banModal');
    if (e.target === modal) {
        closeBanModal();
    }
});
</script>


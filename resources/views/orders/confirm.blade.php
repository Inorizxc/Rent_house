@extends('layout')

@section('title', 'Подтверждение заказа')

@section('main_content')
<div class="page-wrapper">
    <div class="confirm-container" style="max-width: 600px; margin: 40px auto; padding: 24px; background: #ffffff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h1 class="confirm-title" style="font-size: 24px; font-weight: 600; margin-bottom: 24px; color: #111827;">Подтверждение заказа</h1>

        @if(session('error'))
            <div class="message message-error" style="margin-bottom: 16px; padding: 12px; border-radius: 8px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="message message-success" style="margin-bottom: 16px; padding: 12px; border-radius: 8px; background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;">
                {{ session('success') }}
            </div>
        @endif

        <div class="timer-warning" id="timerWarning" style="margin-bottom: 24px; padding: 12px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; color: #92400e;">
            <strong>⏰ Внимание!</strong> У вас осталось <span id="timer">10:00</span> минут для подтверждения заказа. После истечения времени даты будут освобождены.
        </div>

        <div class="house-info" style="margin-bottom: 24px; padding: 16px; background: #f9fafb; border-radius: 8px;">
            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 12px; color: #111827;">Информация о доме</h2>
            <div style="margin-bottom: 8px;">
                <strong>Адрес:</strong> {{ $house->adress ?? '—' }}
            </div>
            <div style="margin-bottom: 8px;">
                <strong>Площадь:</strong> {{ $house->area ? $house->area . ' м²' : '—' }}
            </div>
            <div>
                <strong>Стоимость:</strong> 
                @if($house->price_id)
                    {{ number_format($house->price_id, 0, ',', ' ') }} ₽
                @else
                    Не указана
                @endif
            </div>
        </div>

        <div class="order-details" style="margin-bottom: 24px; padding: 16px; background: #f9fafb; border-radius: 8px;">
            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 12px; color: #111827;">Детали заказа</h2>
            <div style="margin-bottom: 8px;">
                <strong>Дата заезда:</strong> {{ \Carbon\Carbon::parse($checkin_date)->format('d.m.Y') }}
            </div>
            <div style="margin-bottom: 8px;">
                <strong>Дата выезда:</strong> {{ \Carbon\Carbon::parse($checkout_date)->format('d.m.Y') }}
            </div>
            <div style="margin-bottom: 8px;">
                <strong>Количество дней:</strong> {{ $day_count }}
            </div>
        </div>

        <div class="confirm-actions" style="display: flex; gap: 12px;">
            <button id="confirmButton" onclick="confirmOrder()" style="flex: 1; padding: 12px 24px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                Подтвердить
            </button>
            <button id="cancelButton" onclick="cancelOrder()" style="flex: 1; padding: 12px 24px; background: #ef4444; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                Отказаться
            </button>
        </div>
    </div>
</div>

<script>
    const houseId = {{ $house->house_id }};
    const checkinDate = '{{ $checkin_date }}';
    const checkoutDate = '{{ $checkout_date }}';
    const temporaryBlockId = {{ $temporary_block_id }};
    
    let timeLeft = 600; // 10 минут в секундах
    let timerInterval;

    // Таймер обратного отсчета
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timer').textContent = 
            `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            // Автоматически отменяем заказ при истечении времени
            cancelOrder(true);
        }
        timeLeft--;
    }

    // Запускаем таймер
    timerInterval = setInterval(updateTimer, 1000);
    updateTimer();

    // Подтверждение заказа
    async function confirmOrder() {
        const confirmButton = document.getElementById('confirmButton');
        const cancelButton = document.getElementById('cancelButton');
        
        confirmButton.disabled = true;
        cancelButton.disabled = true;
        confirmButton.textContent = 'Обработка...';
        
        clearInterval(timerInterval);

        // Используем форму для отправки данных (чтобы правильно обработать redirect)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/house/${houseId}/order/confirm`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const checkinInput = document.createElement('input');
        checkinInput.type = 'hidden';
        checkinInput.name = 'checkin_date';
        checkinInput.value = checkinDate;
        form.appendChild(checkinInput);
        
        const checkoutInput = document.createElement('input');
        checkoutInput.type = 'hidden';
        checkoutInput.name = 'checkout_date';
        checkoutInput.value = checkoutDate;
        form.appendChild(checkoutInput);
        
        const blockIdInput = document.createElement('input');
        blockIdInput.type = 'hidden';
        blockIdInput.name = 'temporary_block_id';
        blockIdInput.value = temporaryBlockId;
        form.appendChild(blockIdInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    // Отмена заказа
    async function cancelOrder(isExpired = false) {
        const confirmButton = document.getElementById('confirmButton');
        const cancelButton = document.getElementById('cancelButton');
        
        if (!isExpired) {
            if (!confirm('Вы уверены, что хотите отменить заказ?')) {
                return;
            }
        }
        
        confirmButton.disabled = true;
        cancelButton.disabled = true;
        cancelButton.textContent = 'Обработка...';
        
        clearInterval(timerInterval);

        try {
            const response = await fetch(`/house/${houseId}/order/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    temporary_block_id: temporaryBlockId
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Перенаправляем на страницу чата
                window.location.href = `/house/${houseId}/chat`;
            } else {
                throw new Error(data.error || 'Ошибка при отмене заказа');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Ошибка при отмене заказа');
            // Все равно перенаправляем на страницу чата
            window.location.href = `/house/${houseId}/chat`;
        }
    }
</script>
@endsection


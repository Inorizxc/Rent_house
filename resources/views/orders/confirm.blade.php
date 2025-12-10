@extends('layout')

@section('title', 'Подтверждение заказа')

@section('main_content')
<div class="page-wrapper confirm-page-wrapper">
    <div class="confirm-container">
        <h1 class="confirm-title">Подтверждение заказа</h1>

        @if(session('error'))
            <div class="message message-error">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="message message-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="timer-warning" id="timerWarning">
            <strong>⏰ Внимание!</strong> У вас осталось <span id="timer">10:00</span> минут для подтверждения заказа. После истечения времени даты будут освобождены.
        </div>

        <div class="house-info">
            <h2>Информация о доме</h2>
            <div>
                <strong>Адрес:</strong> {{ $house->adress ?? '—' }}
            </div>
            <div>
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

        <div class="order-details">
            <h2>Детали заказа</h2>
            <div>
                <strong>Дата заезда:</strong> {{ \Carbon\Carbon::parse($checkin_date)->format('d.m.Y') }}
            </div>
            <div>
                <strong>Дата выезда:</strong> {{ \Carbon\Carbon::parse($checkout_date)->format('d.m.Y') }}
            </div>
            <div>
                <strong>Количество дней:</strong> {{ $day_count }}
            </div>
        </div>

        <div class="confirm-actions">
            <button id="confirmButton" onclick="confirmOrder()">
                Подтвердить
            </button>
            <button id="cancelButton" onclick="cancelOrder()">
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
    
    const expiresAtTimestamp = {{ $expires_at->timestamp }} * 1000;
    
    let timerInterval;

    function getRemainingTime() {
        const now = Date.now();
        const remaining = Math.max(0, Math.floor((expiresAtTimestamp - now) / 1000));
        return remaining;
    }

    function updateTimer() {
        const timeLeft = getRemainingTime();
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timer').textContent = 
            `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            cancelOrder(true);
        return;
        }
    }

    const initialTimeLeft = getRemainingTime();
    if (initialTimeLeft <= 0) {
        cancelOrder(true);
    } else {
        timerInterval = setInterval(updateTimer, 1000);
        updateTimer();
    }


    async function confirmOrder() {
        const confirmButton = document.getElementById('confirmButton');
        const cancelButton = document.getElementById('cancelButton');
        
        confirmButton.disabled = true;
        cancelButton.disabled = true;
        confirmButton.textContent = 'Обработка...';
        
        clearInterval(timerInterval);

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
                window.location.href = `/house/${houseId}/chat`;
            } else {
                throw new Error(data.error || 'Ошибка при отмене заказа');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Ошибка при отмене заказа');
            window.location.href = `/house/${houseId}/chat`;
        }
    }
</script>
@endsection


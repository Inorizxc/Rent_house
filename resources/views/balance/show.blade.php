@extends('layout')

@section('title', 'Пополнение баланса')

@section('main_content')
@vite('resources/css/balance.css')

<div class="content">
    <div class="page-wrapper">

        <div class="head">
            <h1 class="title">Пополнение баланса</h1>
            <p class="subtitle">Введите сумму и данные карты для пополнения.</p>
            @if(session('success'))
                <div class="success">{{ session('success') }}</div>
            @endif
        </div>

        <div class="grid">
            <section class="card">
                
                
                <div class="card-row">
                    <div class="card-chip"></div>
                    <div class="card-brand">PAY</div>
                </div>

                <div class="card-number" id="previewNumber">•••• •••• •••• ••••</div>

                <div class="card-row">
                    <div class="card-col">
                        <div class="card-label">Сумма</div>
                        <div class="card-value" id="previewAmount">0.00 руб</div>
                    </div>

                    <div class="card-col card-col-right">
                        <div class="card-label">Срок</div>
                        <div class="card-value">
                            <span id="previewMM">MM</span>
                            <span id="previewYY">YY</span>
                        </div>
                    </div>
                </div>

                <div class="card-hint">Данные карты не сохраняются</div>
            
            </section>

            <form class="form" action="{{ route('balance.topup') }}" method="POST" novalidate>
                @csrf

                <label class="field">
                    <span>Сумма пополнения</span>
                    <input type="text" class="field-input" name="amount" id="amount" inputmode="decimal" placeholder="Например 1000.00" autocomplete="off" value="{{ old('amount') }}">
                    @error('amount') <small class="error">{{ $message }}</small> @enderror
                </label>

                <label class="field">
                    <span>Номер карты</span>
                    <input type="text" class="field-input mono" name="card_number" id="card_number" inputmode="numeric" maxlength="19" autocomplete="cc-number" placeholder="1234 5678 9012 3456" value="{{ old('card_number') }}">
                    @error('card_number') <small class="error">{{ $message }}</small> @enderror
                </label>

                <div class="row">

                    <label class="field">
                        <span>MM</span>
                        <input type="text" class="field-input mono" name="exp_month" id="exp_month" inputmode="numeric" maxlength="2" placeholder="MM" value="{{ old('exp_month') }}">
                        @error('exp_month') <small class="error">{{ $message }}</small> @enderror
                    </label>

                    <label class="field">
                        <span>YY</span>
                        <input type="text" class="field-input mono" name="exp_year" id="exp_year" inputmode="numeric" maxlength="2" placeholder="YY" value="{{ old('exp_year') }}">
                        @error('exp_year') <small class="error">{{ $message }}</small> @enderror
                    </label>

                    <label class="field">
                        <span>CVV</span>
                        <input type="text" class="field-input mono" name="cvv" id="cvv" inputmode="numeric" maxlength="4" autocomplete="cc-csc" placeholder="***">
                        @error('cvv') <small class="error">{{ $message }}</small> @enderror
                    </label>

                </div>

                <button class="btn" type="submit">
                    Пополнить
                </button>
            </form>
        </div>
    </div>
</div>
<script>
  const $ = (id) => document.getElementById(id);

  const amount = $('amount');
  const card   = $('card_number');
  const mm     = $('exp_month');
  const yy     = $('exp_year');

  const pAmount = $('previewAmount');
  const pNumber = $('previewNumber');
  const pMM     = $('previewMM');
  const pYY     = $('previewYY');

  function formatCardNumber(value) {
    const digits = value.replace(/\D/g, '').slice(0, 16);
    return digits.replace(/(.{4})/g, '$1 ').trim();
  }

  function formatAmountInput(raw) {
    let s = raw.replace(/[^\d.,]/g, '').replace(',', '.');
    const dot = s.indexOf('.');
    if (dot !== -1) s = s.slice(0, dot + 1) + s.slice(dot + 1).replace(/\./g, '');
    const parts = s.split('.');
    if (parts[1]) parts[1] = parts[1].slice(0, 2);
    return parts.join('.');
  }

  function toMoneyPreview(v) {
    if (!v) return '0.00 руб';
    const n = Number(v);
    if (Number.isNaN(n)) return '0.00 руб';
    return n.toFixed(2) + ' руб';
  }

  function onlyDigits(el, maxLen) {
    el.addEventListener('input', () => {
      el.value = el.value.replace(/\D/g, '').slice(0, maxLen);
    });
  }


  if (card) {
    card.addEventListener('input', (e) => {
      const el = e.target;
      const start = el.selectionStart;
      const digitsBefore = el.value.slice(0, start).replace(/\D/g, '').length;

      const formatted = formatCardNumber(el.value);
      el.value = formatted;

      let pos = 0, seen = 0;
      while (pos < el.value.length && seen < digitsBefore) {
        if (/\d/.test(el.value[pos])) seen++;
        pos++;
      }
      el.setSelectionRange(pos, pos);

      pNumber.textContent = formatted || '•••• •••• •••• ••••';
    });

    card.addEventListener('paste', () => {
      setTimeout(() => card.dispatchEvent(new Event('input')), 0);
    });
  }

  if (amount) {
    amount.addEventListener('input', () => {
      amount.value = formatAmountInput(amount.value);
      pAmount.textContent = toMoneyPreview(amount.value);
    });

    amount.addEventListener('blur', () => {
      if (!amount.value) { pAmount.textContent = '0.00 руб'; return; }
      const n = Number(amount.value);
      if (Number.isNaN(n)) {
        amount.value = '';
        pAmount.textContent = '0.00 руб';
        return;
      }
      amount.value = n.toFixed(2);
      pAmount.textContent = amount.value + ' руб';
    });
  }

  if (mm) {
    onlyDigits(mm, 2);
    mm.addEventListener('input', () => {
      pMM.textContent = mm.value || 'MM';
    });
    mm.addEventListener('blur', () => {
      if (!mm.value) { pMM.textContent = 'MM'; return; }
      let v = parseInt(mm.value, 10);
      if (Number.isNaN(v)) { mm.value = ''; pMM.textContent = 'MM'; return; }
      v = Math.min(12, Math.max(1, v));
      mm.value = String(v).padStart(2, '0');
      pMM.textContent = mm.value;
    });
  }

  if (yy) {
    onlyDigits(yy, 2);
    yy.addEventListener('input', () => {
      pYY.textContent = yy.value || 'YY';
    });
    yy.addEventListener('blur', () => {
      if (!yy.value) { pYY.textContent = 'YY'; return; }
      yy.value = yy.value.padStart(2, '0').slice(0, 2);
      pYY.textContent = yy.value;
    });
  }

  if (pNumber && card) pNumber.textContent = formatCardNumber(card.value) || '•••• •••• •••• ••••';
  if (pAmount && amount) pAmount.textContent = toMoneyPreview(amount.value);
  if (pMM && mm) pMM.textContent = mm.value || 'MM';
  if (pYY && yy) pYY.textContent = yy.value || 'YY';
</script>

@endsection
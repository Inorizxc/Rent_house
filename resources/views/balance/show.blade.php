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

                <div class="card-number">•••• •••• •••• ••••</div>

                <div class="card-row">
                    <div class="card-col">
                        <div class="card-label">Сумма</div>
                        <div class="card-value">0.00 руб</div>
                    </div>

                    <div class="card-col card-col-right">
                        <div class="card-label">Срок</div>
                        <div class="card-value">
                            <span>MM</span>
                            <span>YY</span>
                        </div>
                    </div>
                </div>

                <div class="card-hint">Данные карты не сохраняються</div>
            
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
@endsection
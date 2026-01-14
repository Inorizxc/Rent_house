@extends('layout')

@section('title', 'Пополнение баланса')

@section('main_content')
@vite('resources/css/balance.css')

<div class="container">
  
    <div class="head">
        <h1 class="title">Пополнение баланса</h1>
        <p class="subtitle">Введите сумму и данные карты для пополнения.</p>
    </div>

    <div class="grid">
        <div class="card">

        </div>

        <form class="form" action="" method="">

            <label class="field">
                <span>Сумма пополнения</span>
                <input type="text" class="">
            </label>

            <label class="field">
                <span>Номер карты</span>
                <input type="text" class="">
            </label>

            <div class="row">

                <label class="field">
                    <span>MM</span>
                    <input type="text" class="">
                </label>

                <label class="field">
                    <span>YY</span>
                    <input type="text" class="">
                </label>

                <label class="field">
                    <span>CVV</span>
                    <input type="text" class="">
                </label>
                
            </div>

            <button class="btn" type="submit">
                Пополнить
            </button>
        </form>
    </div>

</div>
@endsection
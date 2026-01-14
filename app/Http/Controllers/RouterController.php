<?php

namespace App\Http\Controllers;
use App\Models\House;

use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function map()
    {
        $houses = House::active()->with(['photo', 'house_type'])->get();
        $houses = House::with(['photo', 'house_type'])->get();
        return view('map', ['houses' => $houses]);
    }

    public function balance()
    {
        return view('balance.show');
    }

    public function balanceTopup(Request $request)
    {
        $request->merge([
            'card_number' => preg_replace('/\D/', '', (string) $request->input('card_number')),
        ]);

        $validated = $request->validate([
            'amount'      => ['required', 'numeric', 'min:1', 'max:10000000'],
            'card_number' => ['required', 'digits:16'],
            'exp_month'   => ['required', 'integer', 'between:1,12'],
            'exp_year'    => ['required', 'integer', 'between:0,99'],
            'cvv'         => ['required', 'digits_between:3,4'],
        ], [
            'amount.required' => 'Введите сумму пополнения.',
            'amount.numeric'  => 'Сумма должна быть числом.',
            'amount.min'      => 'Минимальная сумма пополнения: 1.',
            'card_number.digits' => 'Номер карты должен содержать 16 цифр.',
            'exp_month.between'  => 'Месяц должен быть от 1 до 12.',
            'exp_year.between'   => 'Год укажите двумя цифрами (например 26).',
            'cvv.digits_between' => 'CVV должен быть 3 или 4 цифры.',
        ]);

        return redirect()
            ->route('balance')
            ->with('success', 'Данные приняты. Пополнение обрабатывается.');
    }
}

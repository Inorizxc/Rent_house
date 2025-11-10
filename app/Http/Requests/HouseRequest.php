<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Разрешаем всем, или можешь добавить проверку прав
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['nullable','integer','exists:users,user_id'],
            'adress'        => ['required','string','max:255'],
            'area'          => ['nullable','numeric'],
            'price_id'      => ['nullable','numeric'],
            'rent_type_id'  => ['nullable','integer'],
            'house_type_id' => ['nullable','integer'],
            'calendar_id'   => ['nullable','integer'],
            'lng'           => ['nullable','numeric'],
            'lat'           => ['nullable','numeric'],
            'is_deleted'    => ['required','in:0,1'],
            'image'         => ['nullable','image','max:4096'],
        ];
    }
}

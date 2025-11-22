<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Для создания: проверяем, может ли пользователь создавать дома
        if ($this->routeIs('houses.store')) {
            $user = $this->user();
            return $user && $user->canCreateHouse();
        }

        // Для обновления: проверяем, может ли пользователь редактировать дом
        if ($this->routeIs('houses.update')) {
            $house = $this->route('house'); // house - это параметр маршрута из route model binding
            $user = $this->user();
            return $user && $house && $user->canEditHouse($house);
        }

        // По умолчанию разрешаем (дополнительная проверка в контроллере)
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'         => ['nullable','integer','exists:users,user_id'],
            'adress'          => ['required','string','max:255'],
            'area'            => ['nullable','numeric'],
            'price_id'        => ['nullable','numeric'],
            'rent_type_id'    => ['nullable','integer'],
            'rent_type_name'  => ['nullable','string','exists:rent_types,name'],
            'house_type_id'   => ['nullable','integer'],
            'house_type_name' => ['nullable','string','exists:house_types,name'],
            'calendar_id'     => ['nullable','integer'],
            'lng'             => ['nullable','numeric'],
            'lat'             => ['nullable','numeric'],
            'is_deleted'      => ['required','in:0,1'],
            'image'           => ['nullable','image','max:4096'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->routeIs('houses.store')) {
            $user = $this->user();
            return $user && $user->canCreateHouse();
        }

        if ($this->routeIs('houses.update')) {
            $house = $this->route('house'); 
            $user = $this->user();
            return $user && $house && $user->canEditHouse($house);
        }

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
            'images.*'        => ['nullable','image','mimes:jpeg,png,jpg,gif','max:4096'],
            'deleted_photos'  => ['nullable','string'],
        ];
    }
}

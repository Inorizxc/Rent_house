<?php

namespace App\Services\HouseServices;


use App\Models\RentType;
use App\Models\HouseType;
use App\Models\House;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Services\YandexGeocoder;
use Illuminate\Support\Facades\Cache;

class HouseService{

    public function getHousesOfUser(User $user){


        return House::where('user_id', $user->user_id)
            ->where(function($q) {
                $q->whereNull('is_deleted')
                  ->orWhere('is_deleted', false);
            })
            ->with('photo')
            ->orderBy('house_id', 'desc')
            ->get();;
    }


    public function convertRentNameTypeInId($data){
        if (isset($data['rent_type_name']) && !empty($data['rent_type_name'])) {
            $rentType = RentType::where('name', $data['rent_type_name'])->first();
            if ($rentType) {
                $data['rent_type_id'] = $rentType->rent_type_id;
            } else {
                return redirect()->back()->withErrors(['rent_type_name' => 'Тип аренды не найден'])->withInput();
            }
            unset($data['rent_type_name']);
        }
        return $data;
    }
    public function convertHouseNameTypeInId($data){
        if (isset($data['house_type_name']) && !empty($data['house_type_name'])) {
            $houseType = HouseType::where('name', $data['house_type_name'])->first();
            if ($houseType) {
                $data['house_type_id'] = $houseType->house_type_id;
            } else {
                return redirect()->back()->withErrors(['house_type_name' => 'Тип дома не найден'])->withInput();
            }
            unset($data['house_type_name']);
        }
        return $data;
    }
    public function getGeocoderCoordinat($data){
        if (!empty($data['adress'])) {
            $geocoder = new YandexGeocoder();
            $coordinates = $geocoder->getCoordinates($data['adress']);
            
            if ($coordinates && isset($coordinates['lat']) && isset($coordinates['lng'])) {
                // Конвертируем в строку, так как в БД поле TEXT
                $data['lat'] = (string) $coordinates['lat'];
                $data['lng'] = (string) $coordinates['lng'];
                
                // Проверяем уникальность координат (адреса) при создании
                $existingHouse = House::where('lat', $data['lat'])
                    ->where('lng', $data['lng'])
                    ->where(function($query) {
                        // Исключаем удаленные дома из проверки
                        $query->where('is_deleted', '0')
                            ->orWhereNull('is_deleted');
                    })
                    ->first();
                
                if ($existingHouse) {
                    return redirect()->back()
                        ->withErrors(['adress' => 'Дом с таким адресом (координатами) уже существует. Адрес существующего дома: ' . ($existingHouse->adress ?? 'не указан')])
                        ->withInput();
                }
            } else {
                // Геокодер не смог найти координаты - возвращаем ошибку
                return redirect()->back()
                    ->withErrors(['adress' => 'Не удалось определить координаты для указанного адреса. Пожалуйста, проверьте правильность адреса. Убедитесь, что указан правильный город и название улицы (например, "Саратов, улица Исаева, 5" или "Энгельс, улица Исаева, 5"). Проверьте логи для детальной информации.'])
                    ->withInput();
            }
        } else {
            // Адрес обязателен, так что эта ситуация не должна возникнуть благодаря валидации
            return redirect()->back()
                ->withErrors(['adress' => 'Адрес обязателен для заполнения.'])
                ->withInput();
        }
    }

    public function processMultiplePhotos($request,$house){
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                Photo::saveUploadedFile($image, $house);
            }
        }
    }

    public function AdminRentDeallerCheck($currentUser){
        if ($currentUser && $currentUser->isAdmin()) {
            $users = User::orderBy('name')->get(['user_id','name','sename','patronymic']);
        } else {
            $users = collect([$currentUser])->map(function($user) {
                return (object)[
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'sename' => $user->sename,
                    'patronymic' => $user->patronymic
                ];
            });
        }
        return $users; //
    }
    public function checkValidCoordinates($coordinates){
        if ($coordinates && isset($coordinates['lat']) && isset($coordinates['lng'])) {
                return response()->json([
                    'success' => true,
                    'lat' => (string) $coordinates['lat'],
                    'lng' => (string) $coordinates['lng'],
                    'message' => 'Координаты успешно получены'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось определить координаты для указанного адреса. Пожалуйста, проверьте правильность адреса.'
                ], 404);
            }
    }

    public function processDeletePhoto($request,$house){
        if ($request->has('deleted_photos') && !empty($request->input('deleted_photos'))) {
            $deletedPhotosIds = json_decode($request->input('deleted_photos'), true);
            if (is_array($deletedPhotosIds)) {
                foreach ($deletedPhotosIds as $photoId) {
                    $photo = Photo::find($photoId);
                    if ($photo && $photo->house_id == $house->house_id) {
                        // Удаляем файл из хранилища
                        if ($photo->path && Storage::disk('public')->exists($photo->path)) {
                            Storage::disk('public')->delete($photo->path);
                        }
                        // Удаляем запись из БД
                        $photo->delete();
                    }
                }
            }
        }
    }



}
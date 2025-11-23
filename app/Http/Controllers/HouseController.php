<?php

namespace App\Http\Controllers;


use App\Models\House;
use App\Models\User;
use App\Models\Photo;
use App\Models\RentType;
use App\Models\HouseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Requests\HouseRequest;
use App\Services\YandexGeocoder;

class HouseController extends Controller
{
    //
    public function index(){
        $houses = House::orderBy("timestamp", "desc")->get();
        return view("houses.index", ["houses"=>$houses]);
    }
    public function show(string $id)
    {
        $house = House::with(['user'])->findOrFail($id);
        return view('houses.show', compact('house'));
    }

    public function create()
    {
        // Проверяем, может ли пользователь создавать дома
        $this->authorize('create', House::class);

        $house = new House();
        $currentUser = auth()->user();
        
        // Для администраторов показываем всех пользователей, для арендодателей - только их
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
        
        $rentTypes = RentType::orderBy('name')->get(['rent_type_id', 'name']);
        $houseTypes = HouseType::orderBy('name')->get(['house_type_id', 'name']);
        
        return view('houses.create', compact('house', 'users', 'rentTypes', 'houseTypes', 'currentUser'));
    }


    


    /**
     * Store a newly created resource in storage.
     */
    public function store(HouseRequest $request)
    {
        // Проверяем, может ли пользователь создавать дома
        $this->authorize('create', House::class);

        $data = $request->validated();
        unset($data['image']); // поле не хранится в таблице

        // Конвертируем названия типов в ID
        if (isset($data['rent_type_name']) && !empty($data['rent_type_name'])) {
            $rentType = RentType::where('name', $data['rent_type_name'])->first();
            if ($rentType) {
                $data['rent_type_id'] = $rentType->rent_type_id;
            } else {
                return redirect()->back()->withErrors(['rent_type_name' => 'Тип аренды не найден'])->withInput();
            }
            unset($data['rent_type_name']);
        }
        
        // Проверяем, что rent_type_id установлен (обязательное поле)
        // Если не установлен напрямую и не был установлен через rent_type_name
        if (!isset($data['rent_type_id']) || $data['rent_type_id'] === '' || $data['rent_type_id'] === null) {
            \Log::warning('rent_type_id не установлен', ['data_keys' => array_keys($data), 'rent_type_name' => $data['rent_type_name'] ?? 'не передано']);
            return redirect()->back()->withErrors(['rent_type_name' => 'Тип аренды обязателен для заполнения'])->withInput();
        }
        // Конвертируем в строку, так как в БД поле TEXT
        $data['rent_type_id'] = (string)$data['rent_type_id'];
        
        if (isset($data['house_type_name']) && !empty($data['house_type_name'])) {
            $houseType = HouseType::where('name', $data['house_type_name'])->first();
            if ($houseType) {
                $data['house_type_id'] = $houseType->house_type_id;
            } else {
                return redirect()->back()->withErrors(['house_type_name' => 'Тип дома не найден'])->withInput();
            }
            unset($data['house_type_name']);
        }
        
        // Проверяем, что house_type_id установлен (обязательное поле)
        // Если не установлен напрямую и не был установлен через house_type_name
        if (!isset($data['house_type_id']) || $data['house_type_id'] === '' || $data['house_type_id'] === null) {
            \Log::warning('house_type_id не установлен', ['data_keys' => array_keys($data), 'house_type_name' => $data['house_type_name'] ?? 'не передано']);
            return redirect()->back()->withErrors(['house_type_name' => 'Тип дома обязателен для заполнения'])->withInput();
        }
        // Конвертируем в строку, так как в БД поле TEXT
        $data['house_type_id'] = (string)$data['house_type_id'];

        // Если пользователь не админ, автоматически устанавливаем его как владельца
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isAdmin()) {
            $data['user_id'] = $currentUser->user_id;
        }

        // Автоматическое получение координат по адресу через геокодер
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
                    \Log::warning('Попытка создать дом с существующими координатами', [
                        'address' => $data['adress'],
                        'lat' => $data['lat'],
                        'lng' => $data['lng'],
                        'existing_house_id' => $existingHouse->house_id,
                        'existing_address' => $existingHouse->adress
                    ]);
                    return redirect()->back()
                        ->withErrors(['adress' => 'Дом с таким адресом (координатами) уже существует. Адрес существующего дома: ' . ($existingHouse->adress ?? 'не указан')])
                        ->withInput();
                }
            } else {
                // Геокодер не смог найти координаты - возвращаем ошибку
                \Log::warning('Не удалось получить координаты для адреса', [
                    'address' => $data['adress']
                ]);
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

        // Финальная проверка перед созданием - убеждаемся, что обязательные поля присутствуют
        if (!isset($data['rent_type_id']) || $data['rent_type_id'] === '' || $data['rent_type_id'] === null) {
            \Log::error('КРИТИЧЕСКАЯ ОШИБКА: rent_type_id отсутствует перед созданием!', [
                'data_keys' => array_keys($data),
                'rent_type_id_value' => $data['rent_type_id'] ?? 'не установлено',
                'rent_type_name' => $data['rent_type_name'] ?? 'не передано'
            ]);
            return redirect()->back()->withErrors(['rent_type_name' => 'Тип аренды обязателен для заполнения'])->withInput();
        }
        
        if (!isset($data['house_type_id']) || $data['house_type_id'] === '' || $data['house_type_id'] === null) {
            \Log::error('КРИТИЧЕСКАЯ ОШИБКА: house_type_id отсутствует перед созданием!', [
                'data_keys' => array_keys($data),
                'house_type_id_value' => $data['house_type_id'] ?? 'не установлено',
                'house_type_name' => $data['house_type_name'] ?? 'не передано'
            ]);
            return redirect()->back()->withErrors(['house_type_name' => 'Тип дома обязателен для заполнения'])->withInput();
        }

        // Явно устанавливаем все поля напрямую, чтобы гарантировать их наличие
        $house = new House();
        
        // Устанавливаем все поля из массива $data
        foreach ($data as $key => $value) {
            if (in_array($key, $house->getFillable()) || $key === 'rent_type_id' || $key === 'house_type_id') {
                $house->$key = $value;
            }
        }
        
        // Убеждаемся, что обязательные поля установлены (повторная установка для гарантии)
        $house->rent_type_id = (string)$data['rent_type_id'];
        $house->house_type_id = (string)$data['house_type_id'];
        
        $house->save();

        // Обработка множественных изображений
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                Photo::saveUploadedFile($image, $house);
            }
        }

        return redirect()->route('houses.index')->with('ok', 'Дом создан');
    }

    public function edit(House $house)
    {
        // Проверяем, может ли пользователь редактировать этот дом
        $this->authorize('update', $house);

        // Загружаем связи для отображения текущих значений
        $house->load(['rent_type', 'house_type', 'photo']);

        $currentUser = auth()->user();
        
        // Для администраторов показываем всех пользователей, для арендодателей - только их
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
        
        $rentTypes = RentType::orderBy('name')->get(['rent_type_id', 'name']);
        $houseTypes = HouseType::orderBy('name')->get(['house_type_id', 'name']);
        
        return view('houses.edit', compact('house', 'users', 'rentTypes', 'houseTypes', 'currentUser'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HouseRequest $request, House $house)
    {
        // Проверяем, может ли пользователь редактировать этот дом
        $this->authorize('update', $house);

        $data = $request->validated();

        // Конвертируем названия типов в ID
        if (isset($data['rent_type_name']) && !empty($data['rent_type_name'])) {
            $rentType = RentType::where('name', $data['rent_type_name'])->first();
            if ($rentType) {
                $data['rent_type_id'] = $rentType->rent_type_id;
            }
            unset($data['rent_type_name']);
        }
        
        if (isset($data['house_type_name']) && !empty($data['house_type_name'])) {
            $houseType = HouseType::where('name', $data['house_type_name'])->first();
            if ($houseType) {
                $data['house_type_id'] = $houseType->house_type_id;
            }
            unset($data['house_type_name']);
        }

        // Если пользователь не админ, не позволяем менять владельца
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isAdmin()) {
            unset($data['user_id']);
        }

        // Автоматическое получение координат по адресу через геокодер
        if (!empty($data['adress'])) {
            $geocoder = new YandexGeocoder();
            $coordinates = $geocoder->getCoordinates($data['adress']);
            
            if ($coordinates && isset($coordinates['lat']) && isset($coordinates['lng'])) {
                // Конвертируем в строку, так как в БД поле TEXT
                $data['lat'] = (string) $coordinates['lat'];
                $data['lng'] = (string) $coordinates['lng'];
                
                // Проверяем уникальность координат (адреса), исключая текущий дом
                $existingHouse = House::where('lat', $data['lat'])
                    ->where('lng', $data['lng'])
                    ->where('house_id', '!=', $house->house_id) // Исключаем текущий дом
                    ->where(function($query) {
                        // Исключаем удаленные дома из проверки
                        $query->where('is_deleted', '0')
                              ->orWhereNull('is_deleted');
                    })
                    ->first();
                
                if ($existingHouse) {
                    \Log::warning('Попытка обновить дом на существующие координаты', [
                        'address' => $data['adress'],
                        'lat' => $data['lat'],
                        'lng' => $data['lng'],
                        'house_id' => $house->house_id,
                        'existing_house_id' => $existingHouse->house_id,
                        'existing_address' => $existingHouse->adress
                    ]);
                    return redirect()->back()
                        ->withErrors(['adress' => 'Дом с таким адресом (координатами) уже существует. Адрес: ' . ($existingHouse->adress ?? 'не указан')])
                        ->withInput();
                }
            } else {
                // Геокодер не смог найти координаты - возвращаем ошибку
                \Log::warning('Не удалось получить координаты для адреса при обновлении', [
                    'address' => $data['adress'],
                    'house_id' => $house->house_id
                ]);
                return redirect()->back()
                    ->withErrors(['adress' => 'Не удалось определить координаты для указанного адреса. Пожалуйста, уточните адрес или попробуйте другой формат (например, "Саратов, ул. Исаева, 3").'])
                    ->withInput();
            }
        } else {
            // Если адрес пустой, но валидация требует его, это обработается валидацией
            // Если адрес не указан, но дом существует, оставляем старые координаты
            if (empty($data['adress']) && $house->adress) {
                // Адрес удален, но координаты оставляем старые (на случай если адрес был временно удален)
                // Если адрес обязателен, валидация не пропустит
            }
        }

        // Обработка удаления фотографий
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
        
        // Обработка множественных изображений (если загружено)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                Photo::saveUploadedFile($image, $house);
            }
        }
            
        $house->update($data);
        return redirect()->route('houses.index')->with('ok', 'Изменения сохранены');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(House $house)
    {
        // Проверяем, может ли пользователь удалять этот дом
        $this->authorize('delete', $house);

        // удалим возможную картинку
        foreach (['jpg','jpeg','png','webp','gif'] as $ext) {
            $old = "houses/{$house->house_id}.{$ext}";
            if (Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }
        $house->delete();

        return redirect()->route('houses.index')->with('ok','Дом удалён');
    }

    /**
     * Получает координаты для указанного адреса через AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoordinates(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500'
        ]);

        $address = $request->input('address');
        
        if (empty($address)) {
            return response()->json([
                'success' => false,
                'message' => 'Адрес не указан'
            ], 400);
        }

        // Декодируем адрес, если он был закодирован при передаче через JSON
        // Laravel автоматически декодирует JSON, но на всякий случай проверяем
        if (preg_match('/%[0-9A-Fa-f]{2}/', $address)) {
            $address = urldecode($address);
        }

        try {
            $geocoder = new YandexGeocoder();
            $coordinates = $geocoder->getCoordinates($address);
            
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
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении координат через AJAX', [
                'address' => $address,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при получении координат. Попробуйте позже.'
            ], 500);
        }
    }


}

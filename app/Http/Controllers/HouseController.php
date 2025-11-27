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
use App\Services\HouseServices\HouseService as HouseService;

class HouseController extends Controller
{
    //
    public function index(){
        $houses = House::active()->orderBy("timestamp", "desc")->get();
        return view("houses.index", ["houses"=>$houses]);
    }
    public function show(string $id)
    {
        $house = House::with(['user'])->findOrFail($id);
        
        // Проверяем, не забанен ли дом и не удален ли он
        if ($house->is_deleted || $house->isBanned()) {
            abort(404, 'Дом не найден или недоступен');
        }
        
        return view('houses.show', compact('house'));
    }

    public function create()
    {
        // Проверяем, может ли пользователь создавать дома
        $this->authorize('create', House::class);
        
        $currentUser = auth()->user();
        
        // Проверяем, не забанен ли пользователь
        if ($currentUser && $currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $message = $currentUser->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете создавать дома.'
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете создавать дома до этой даты.";
            
            return redirect()->back()->with('error', $message);
        }

        $house = new House();
        
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
        $houseService = app(HouseService::class);
        // Проверяем, может ли пользователь создавать дома
        $this->authorize('create', House::class);
        
        $currentUser = auth()->user();
        
        // Проверяем, не забанен ли пользователь
        if ($currentUser && $currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $message = $currentUser->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете создавать дома.'
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете создавать дома до этой даты.";
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message
                ], 403);
            }
            
            return redirect()->back()->with('error', $message)->withInput();
        }

        $data = $request->validated();
        unset($data['image']); // поле не хранится в таблице

        // Конвертируем названия типов в ID
        $data=$houseService->convertRentNameTypeInId($data);
        
        // Проверяем, что rent_type_id установлен (обязательное поле)
        // Если не установлен напрямую и не был установлен через rent_type_name
        if (!isset($data['rent_type_id']) || $data['rent_type_id'] === '' || $data['rent_type_id'] === null) {
            return redirect()->back()->withErrors(['rent_type_name' => 'Тип аренды обязателен для заполнения'])->withInput();
        }
        // Конвертируем в строку, так как в БД поле TEXT
        $data['rent_type_id'] = (string)$data['rent_type_id'];
        
        $data=$houseService->convertHouseNameTypeInId($data);
        
        // Проверяем, что house_type_id установлен (обязательное поле)
        // Если не установлен напрямую и не был установлен через house_type_name
        if (!isset($data['house_type_id']) || $data['house_type_id'] === '' || $data['house_type_id'] === null) {
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
        $houseService->getGeocoderCoordinat($data);

        // Финальная проверка перед созданием - убеждаемся, что обязательные поля присутствуют
        if (!isset($data['rent_type_id']) || $data['rent_type_id'] === '' || $data['rent_type_id'] === null) {
            return redirect()->back()->withErrors(['rent_type_name' => 'Тип аренды обязателен для заполнения'])->withInput();
        }
        
        if (!isset($data['house_type_id']) || $data['house_type_id'] === '' || $data['house_type_id'] === null) {
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
        $houseService -> processMultiplePhotos($request,$house);

        return redirect()->route('houses.index')->with('ok', 'Дом создан');
    }

    public function edit(House $house)
    {
        $houseService = app(HouseService::class);
        // Проверяем, может ли пользователь редактировать этот дом
        $this->authorize('update', $house);

        // Загружаем связи для отображения текущих значений
        $house->load(['rent_type', 'house_type', 'photo', 'house_calendar']);

        $currentUser = auth()->user();
        
        // Для администраторов показываем всех пользователей, для арендодателей - только их
        $users=$houseService->AdminRentDeallerCheck($currentUser);
        
        $rentTypes = RentType::orderBy('name')->get(['rent_type_id', 'name']);
        $houseTypes = HouseType::orderBy('name')->get(['house_type_id', 'name']);
        
        return view('houses.edit', compact('house', 'users', 'rentTypes', 'houseTypes', 'currentUser'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HouseRequest $request, House $house)
    {
        $houseService = app(HouseService::class);
        // Проверяем, может ли пользователь редактировать этот дом
        $this->authorize('update', $house);
        
        $currentUser = auth()->user();
        
        // Проверяем, не забанен ли пользователь
        if ($currentUser && $currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $message = $currentUser->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете редактировать дома.'
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете редактировать дома до этой даты.";
            
            return redirect()->back()->with('error', $message)->withInput();
        }

        $data = $request->validated();

        // Конвертируем названия типов в ID
        $data=$houseService->convertRentNameTypeInId($data);
        
        $data=$houseService->convertHouseNameTypeInId($data);

        // Если пользователь не админ, не позволяем менять владельца
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isAdmin()) {
            unset($data['user_id']);
        }

        // Автоматическое получение координат по адресу через геокодер
        $houseService->getGeocoderCoordinat($data);

        // Обработка удаления фотографий
        $houseService->processDeletePhoto($request,$house);
        
        // Обработка множественных изображений (если загружено)
        $houseService->processMultiplePhotos($request,$house);
            
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
        
        $currentUser = auth()->user();
        
        // Проверяем, не забанен ли пользователь
        if ($currentUser && $currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $message = $currentUser->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете удалять дома.'
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете удалять дома до этой даты.";
            
            return redirect()->back()->with('error', $message);
        }

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
        $houseService = app(HouseService::class);
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
            
            return $houseService->checkValidCoordinates($coordinates);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при получении координат. Попробуйте позже.'
            ], 500);
        }
    }

    /**
     * Получает подсказки адресов для автодополнения через AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddressSuggestions(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:500'
        ]);

        $query = $request->input('query');
        
        if (empty($query) || strlen(trim($query)) < 2) {
            return response()->json([
                'success' => true,
                'suggestions' => []
            ]);
        }

        try {
            $geocoder = new YandexGeocoder();
            $suggestions = $geocoder->getAddressSuggestions($query, 3); // Получаем максимум 3 подсказки
            
            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении подсказок адресов', [
                'query' => $query,
                'exception' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'suggestions' => [],
                'message' => 'Произошла ошибка при получении подсказок. Попробуйте позже.'
            ], 500);
        }
    }


}

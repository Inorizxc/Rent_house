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
        return redirect()->route('map');
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

        $this->authorize('create', House::class);

        $currentUser = auth()->user();

        if ($currentUser && $currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $banReason = $currentUser->ban_reason ? "\n\nПричина: {$currentUser->ban_reason}" : '';
            $message = $currentUser->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете создавать дома.' . $banReason
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете создавать дома до этой даты." . $banReason;
            
            return redirect()->back()->with('error', $message);
        }

        $house = new House();

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

    public function store(HouseRequest $request)
    {
        $houseService = app(HouseService::class);
        $this->authorize('create', House::class);
        
        $currentUser = auth()->user();

        if ($currentUser && $currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $banReason = $currentUser->ban_reason ? "\n\nПричина: {$currentUser->ban_reason}" : '';
            $message = $currentUser->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете создавать дома.' . $banReason
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете создавать дома до этой даты." . $banReason;
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message
                ], 403);
            }
            
            return redirect()->back();
        }

        $data = $request->validated();

        if($data['prepayment']==null || (float)$data['prepayment']>100){
            $prepayment='30';
        }
        else{
            $prepayment = $data['prepayment'];
        }

        unset($data['image']);

        $data=$houseService->convertRentNameTypeInId($data);

        if (!isset($data['rent_type_id']) || $data['rent_type_id'] === '' || $data['rent_type_id'] === null) {
            return redirect()->back();
        }

        $data['rent_type_id'] = (string)$data['rent_type_id'];
        
        $data=$houseService->convertHouseNameTypeInId($data);

        if (!isset($data['house_type_id']) || $data['house_type_id'] === '' || $data['house_type_id'] === null) {
            return redirect()->back();
        }

        $data['house_type_id'] = (string)$data['house_type_id'];

        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isAdmin()) {
            $data['user_id'] = $currentUser->user_id;
        }

        $houseService->getGeocoderCoordinat($data);

        if (!isset($data['rent_type_id']) || $data['rent_type_id'] === '' || $data['rent_type_id'] === null) {
            return redirect()->back();
        }
        
        if (!isset($data['house_type_id']) || $data['house_type_id'] === '' || $data['house_type_id'] === null) {
            return redirect()->back();
        }

        $house = new House();

        foreach ($data as $key => $value) {
            if (in_array($key, $house->getFillable()) || $key === 'rent_type_id' || $key === 'house_type_id') {
                $house->$key = $value;
            }
        }

        $house->rent_type_id = (string)$data['rent_type_id'];
        $house->house_type_id = (string)$data['house_type_id'];
        
        $house->save();

        $houseService -> processMultiplePhotos($request,$house);

        return redirect()->route('houses.index');
    }

    public function edit(House $house)
    {
        $houseService = app(HouseService::class);
        $this->authorize('update', $house);

        $house->load(['rent_type', 'house_type', 'photo', 'house_calendar']);

        $currentUser = auth()->user();

        $users=$houseService->AdminRentDeallerCheck($currentUser);
        
        $rentTypes = RentType::orderBy('name')->get(['rent_type_id', 'name']);
        $houseTypes = HouseType::orderBy('name')->get(['house_type_id', 'name']);
        
        return view('houses.edit', compact('house', 'users', 'rentTypes', 'houseTypes', 'currentUser'));
    }

    public function update(HouseRequest $request, House $house)
    {
        $houseService = app(HouseService::class);
        $this->authorize('update', $house);
        
        $currentUser = auth()->user();

        if ($currentUser && $currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $banReason = $currentUser->ban_reason ? "\n\nПричина: {$currentUser->ban_reason}" : '';
            $message = $currentUser->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете редактировать дома.' . $banReason
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете редактировать дома до этой даты." . $banReason;
            
            return redirect()->back();
        }

        $data = $request->validated();
        if($data['prepayment']==null || $data['prepayment']>100 ){
            $data['prepayment']='30';
        }
        
        $data=$houseService->convertRentNameTypeInId($data);
        
        $data=$houseService->convertHouseNameTypeInId($data);

        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isAdmin()) {
            unset($data['user_id']);
        }

        $houseService->getGeocoderCoordinat($data);

        $houseService->processDeletePhoto($request,$house);

        $houseService->processMultiplePhotos($request,$house);
            
        $house->update($data);
        return redirect()->route('houses.index');
    }

    public function destroy(House $house)
    {
        $this->authorize('delete', $house);
        
        $currentUser = auth()->user();

        if ($currentUser && $currentUser->isBanned()) {
            $banUntil = $currentUser->getBanUntilDate();
            $banReason = $currentUser->ban_reason ? "\n\nПричина: {$currentUser->ban_reason}" : '';
            $message = $currentUser->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете удалять дома.' . $banReason
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете удалять дома до этой даты." . $banReason;
            
            return redirect()->back();
        }

        foreach (['jpg','jpeg','png','webp','gif'] as $ext) {
            $old = "houses/{$house->house_id}.{$ext}";
            if (Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }
        $house->delete();

        return redirect()->route('houses.index');
    }

    /**
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
            $suggestions = $geocoder->getAddressSuggestions($query, 3); 
            
            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
        }
    }


}

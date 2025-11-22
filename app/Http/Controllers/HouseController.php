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

        // Если пользователь не админ, автоматически устанавливаем его как владельца
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isAdmin()) {
            $data['user_id'] = $currentUser->user_id;
        }

        $house = House::create($data);

        if ($request->hasFile('image')) {
            Photo::saveUploadedFile($request->file('image'), $house);
        }

        return redirect()->route('houses.index')->with('ok', 'Дом создан');
    }

    public function edit(House $house)
    {
        // Проверяем, может ли пользователь редактировать этот дом
        $this->authorize('update', $house);

        // Загружаем связи для отображения текущих значений
        $house->load(['rent_type', 'house_type']);

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

        // Обработка изображения (если загружено)
        if ($request->hasFile('image')) {
            $validated = $request->validate([
                "image" => 'image|mimes:jpeg,png,jpg,gif|max:4096',
            ]);
            
            $file = $request->file("image");
            $photo = Photo::saveUploadedFile($file, $house);
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


}

<?php

namespace App\Http\Controllers;


use App\Models\House;
use App\Models\User;
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
    public function show(string $id){
        return view("houses.show",["houses"=> House::find ($id)]);
    }

    public function create()
    {
        $house = new House();
        $users = User::orderBy('name')->get(['user_id','name','sename','patronymic']);
        return view('houses.create', compact('house','users'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(HouseRequest $request)
{
    $data = $request->validated();
    unset($data['image']); // поле не хранится в таблице

    $house = House::create($data);

    if ($request->hasFile('image')) {
        $this->storeImage($house, $request->file('image'));
    }

    return redirect()->route('houses.index')->with('ok', 'Дом создан');
}

    /**
     * Display the specified resource.
     */
    
    /**
     * Show the form for editing the specified resource.
     */
<<<<<<< HEAD
    public function edit(House $house)
=======
    public function edit()
>>>>>>> 946bc8ca84f366ebea0e1e83ee58b0e58936d179
    {
        $users = User::orderBy('name')->get(['user_id','name','sename','patronymic']);
        return view('houses.edit', compact('house','users'));
    }

    /**
     * Update the specified resource in storage.
     */
<<<<<<< HEAD
    public function update(HouseRequest $request, House $house)
{
    $data = $request->validated();
    unset($data['image']);

    $house->update($data);

    if ($request->hasFile('image')) {
        $this->storeImage($house, $request->file('image'));
=======
    public function update(Request $request)
    {
        //
>>>>>>> 946bc8ca84f366ebea0e1e83ee58b0e58936d179
    }

    return redirect()->route('houses.index')->with('ok', 'Изменения сохранены');
}

    /**
     * Remove the specified resource from storage.
     */
<<<<<<< HEAD
    public function destroy(House $house)
=======
    public function destroy()
>>>>>>> 946bc8ca84f366ebea0e1e83ee58b0e58936d179
    {
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

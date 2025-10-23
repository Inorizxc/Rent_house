<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;


class HouseController extends Controller
{
    //
    public function index(){
        $houses = House::with('user');
        return view("houses.index", ["houses"=>House::all()]);
    }
    public function show(string $id){
        return view("houses.show",["houses"=> House::find ($id)]);
    }
}

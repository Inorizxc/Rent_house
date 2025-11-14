<?php

namespace App\Http\Controllers;
use App\Models\House;

use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function map()
    {
        $houses = House::with('photo')->get();
        return view('map', ['houses' => $houses]);
    }

    public function map2()
    {
        $houses = House::with('photo')->get();
        return view('map2', ['houses' => $houses]);
    }
}

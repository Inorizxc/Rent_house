<?php

namespace App\Http\Controllers;
use App\Models\House;

use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function map()
    {
        $houses = new House();
        return view('map', ['houses' => $houses->all()]);
    }

    public function map2()
    {
        $houses = House::with('photos')->get();
        return view('map2', ['houses' => $houses->all()]);
    }
}

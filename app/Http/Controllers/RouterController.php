<?php

namespace App\Http\Controllers;
use App\Models\House;

use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function map()
    {
        $houses = House::with(['photo', 'house_type'])->get();
        return view('map', ['houses' => $houses]);
    }
}

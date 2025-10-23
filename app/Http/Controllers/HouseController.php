<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;


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

    public function create(){

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }

}

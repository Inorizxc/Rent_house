<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HouseCalendarController extends Controller
{
    public function index()
    {
        $dates = HouseCalendar::with('house')->get();;
        return view("calendar.index", ["house_calendar"=>$dates]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
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
    public function show(Chat $chat)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HouseCalendar $calendar)
    {
        $calendar->delete();
    }

}

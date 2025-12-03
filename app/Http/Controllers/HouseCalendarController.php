<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HouseCalendar;
use App\Models\House;
use Illuminate\Support\Facades\Auth;

class HouseCalendarController extends Controller
{
    public function index()
    {
        $dates = HouseCalendar::with('house')->get();;
        return view("calendar.index", ["house_calendar"=>$dates]);
    }


    public function create()
    {
        return view('users.create');
    }


    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }


    public function destroy(HouseCalendar $calendar)
    {
        $calendar->delete();
    }

    public function updateDates(Request $request, $houseId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }
        if ($user->isBanned()) {
            $banUntil = $user->getBanUntilDate();
            $banReason = $user->ban_reason ? "\n\nПричина: {$user->ban_reason}" : '';
            $message = $user->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете редактировать календарь.' . $banReason
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете редактировать календарь до этой даты." . $banReason;
            
            return response()->json([
                'success' => false,
                'error' => $message
            ], 403);
        }
        
        $request->validate([
            'date' => 'required|date',
            'action' => 'required|in:add,remove'
        ]);

        $house = House::findOrFail($houseId);

        if ($house->user_id !== Auth::id()) {
            return response()->json(['error' => 'У вас нет прав для изменения этого календаря'], 403);
        }
        $calendar = HouseCalendar::firstOrCreate(
            ['house_id' => $houseId],
            ['dates' => []]
        );

        $dates = $calendar->dates ?? [];
        $dateStr = $request->date;

        if ($request->action === 'add') {
            if (!in_array($dateStr, $dates)) {
                $dates[] = $dateStr;
                sort($dates); 
            }
        } else {
            $dates = array_values(array_filter($dates, function($d) use ($dateStr) {
                return $d !== $dateStr;
            }));
        }

        $calendar->dates = $dates;
        $calendar->save();

        return response()->json([
            'success' => true,
            'dates' => $dates,
            'action' => $request->action
        ]);
    }

    public function updateDatesRange(Request $request, $houseId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }

        if ($user->isBanned()) {
            $banUntil = $user->getBanUntilDate();
            $banReason = $user->ban_reason ? "\n\nПричина: {$user->ban_reason}" : '';
            $message = $user->is_banned_permanently 
                ? 'Ваш аккаунт заблокирован навсегда. Вы не можете редактировать календарь.' . $banReason
                : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете редактировать календарь до этой даты." . $banReason;
            
            return response()->json([
                'success' => false,
                'error' => $message
            ], 403);
        }
        
        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'required|date',
            'action' => 'required|in:add,remove'
        ]);

        $house = House::findOrFail($houseId);

        if ($house->user_id !== Auth::id()) {
            return response()->json(['error' => 'У вас нет прав для изменения этого календаря'], 403);
        }

        $calendar = HouseCalendar::firstOrCreate(
            ['house_id' => $houseId],
            ['dates' => []]
        );

        $dates = $calendar->dates ?? [];
        $newDates = $request->dates;

        if ($request->action === 'add') {
            foreach ($newDates as $dateStr) {
                if (!in_array($dateStr, $dates)) {
                    $dates[] = $dateStr;
                }
            }
            sort($dates); // Сортируем даты
        } else {
            $dates = array_values(array_filter($dates, function($d) use ($newDates) {
                return !in_array($d, $newDates);
            }));
        }

        $calendar->dates = $dates;
        $calendar->save();

        return response()->json([
            'success' => true,
            'dates' => $dates,
            'action' => $request->action
        ]);
    }

}

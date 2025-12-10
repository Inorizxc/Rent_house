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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
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

    /**
     * Update dates for a house calendar
     */
    public function updateDates(Request $request, $houseId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация'
            ], 401);
        }
        
        // Проверяем, не забанен ли пользователь
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
        
        // Проверяем, что пользователь является владельцем дома
        if ($house->user_id !== Auth::id()) {
            return response()->json(['error' => 'У вас нет прав для изменения этого календаря'], 403);
        }

        // Получаем или создаем календарь для дома
        $calendar = HouseCalendar::firstOrCreate(
            ['house_id' => $houseId],
            ['dates' => []]
        );

        $dates = $calendar->dates ?? [];
        $dateStr = $request->date;

        if ($request->action === 'add') {
            // Добавляем дату, если её еще нет
            if (!in_array($dateStr, $dates)) {
                $dates[] = $dateStr;
                sort($dates); // Сортируем даты
            }
        } else {
            // Удаляем дату
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
            // Добавляем даты, которых еще нет
            foreach ($newDates as $dateStr) {
                if (!in_array($dateStr, $dates)) {
                    $dates[] = $dateStr;
                }
            }
            sort($dates); // Сортируем даты
        } else {
            // Удаляем даты
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

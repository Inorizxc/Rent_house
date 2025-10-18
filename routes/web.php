<?php


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;


Route::get('/tables', function () {
    return view('welcom');
})->name('home');

Route::get('/', function () {
    // Получаем список таблиц
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name;");
    $tableNames = collect($tables)->pluck('name');

    // Проверяем, выбрал ли пользователь таблицу
    $selectedTable = request('table');
    $rows = [];

    if ($selectedTable) {
        try {
            $rows = DB::table($selectedTable)->limit(10)->get(); // первые 10 строк
        } catch (\Throwable $e) {
            $rows = collect();
        }
    }

    return view('test', [
        'tables' => $tableNames,
        'selectedTable' => $selectedTable,
        'rows' => $rows,
    ]);
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';

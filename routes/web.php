<?php


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;


Route::get('/tables', function () {
    return view('welcom');
})->name('home');

Route::match(['GET','POST'], '/', function (Request $request) {

    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name;");
    $tableNames = collect($tables)->pluck('name');

    $selectedTable = $request->get('table');

    $columns = collect();
    if ($selectedTable) {
        $columns = collect(DB::select("PRAGMA table_info('$selectedTable')"));
    }

    if ($request->isMethod('post') && $selectedTable) {
        $blocked = ['id','created_at','updated_at','deleted_at'];
        $fillable = $columns
            ->pluck('name')
            ->reject(fn($c) => in_array($c, $blocked, true))
            ->values()
            ->all();

        $payload = $request->only($fillable);

        foreach ($payload as $k => $v) {
            if ($v === '') $payload[$k] = null;
        }

        if (!empty($payload)) {
            DB::table($selectedTable)->insert($payload);
            return redirect('/')
                ->withInput(['table' => $selectedTable])
                ->with('status', "Запись добавлена в «{$selectedTable}»");
        }
    }

    $rows = collect();
    if ($selectedTable) {
        $rows = DB::table($selectedTable)->limit(10)->get();
    }

    return view('test', [
        'tables'        => $tableNames,
        'selectedTable' => $selectedTable,
        'columns'       => $columns,
        'rows'          => $rows,
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

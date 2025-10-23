<?php


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\HouseController;

Route::get('/tables', function () {
    return view('welcom');
})->name('home');

Route::match(['GET','POST'], '/', function (Request $request) {

    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name;");
    $tableNames = collect($tables)->pluck('name');

    $selectedTable = $request->get('table');
    
    $limit = min(max((int)$request->get('per', 10), 1), 100);

    $columns = collect();
    if ($selectedTable) {
        $columns = collect(DB::select("PRAGMA table_info('$selectedTable')"));
    }

    $page = max((int)$request->get('page', 1), 1);

    $columns = collect();
    if ($selectedTable){
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

    $rows   = collect();
    $total  = 0;
    $pages  = 1;
    if ($selectedTable) {
        $total = DB::table($selectedTable)->count();
        $pages = max((int)ceil($total / $limit), 1);
        // держим page в границах [1..pages]
        $page  = min($page, $pages);
        $rows  = DB::table($selectedTable)
            ->offset(($page-1)*$limit)
            ->limit($limit)
            ->get();
    }


    return view('test', [
        'tables'        => $tableNames,
        'selectedTable' => $selectedTable,
        'columns'       => $columns,
        'rows'          => $rows,
        'page'          => $page,
        'pages'         => $pages,
        'total'         => $total,
        'limit'         => $limit,
    ]);
});

Route::get('/users', \App\Livewire\UsersPage::class)->name('users');
Route::get('/houses', \App\Livewire\HousesPage::class)->name('houses');
//Route::get("/houses",[HouseController::class,"index"]); сука кирюха вот что бы все через такое сделал

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

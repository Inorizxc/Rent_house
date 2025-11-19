<?php


use App\Http\Controllers\HouseController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\UserCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;


Route::controller(RouterController::class)->group(function () {
    Route::get('/map', 'map')->name('map');
    Route::get('/map2', 'map2')->name('map.alt');
});

Route::controller(HouseController::class)->group(function () {
    Route::get('/house/{id}', 'show')->name('house.show');
});

Route::prefix('profile/{id}')
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'show')->name('profile.show');
        Route::get('/houses', 'showHouses')->name('users.showHouses');
    });


Route::get('/tables', function () {
    return view('welcome');
})->name('home');

Route::match(['GET', 'POST'], '/', function (Request $request) {
    $tableNames = collect(DB::select("
        SELECT name
        FROM sqlite_master
        WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
        ORDER BY name
    "))->pluck('name');

    $selectedTable = $request->get('table');
    if ($selectedTable && !$tableNames->contains($selectedTable)) {
        $selectedTable = null;
    }

    $limit = (int) $request->get('per', 10);
    $limit = max(1, min($limit, 100));

    $page = max((int) $request->get('page', 1), 1);

    $columns = collect();
    if ($selectedTable) {
        $columns = collect(DB::select("PRAGMA table_info('$selectedTable')"));
    }

    if ($request->isMethod('post') && $selectedTable) {
        $blocked = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $fillable = $columns
            ->pluck('name')
            ->reject(fn ($column) => in_array($column, $blocked, true))
            ->values()
            ->all();

        $payload = $request->only($fillable);
        foreach ($payload as $key => $value) {
            if ($value === '') {
                $payload[$key] = null;
            }
        }

        if (!empty($payload)) {
            DB::table($selectedTable)->insert($payload);

            return redirect('/')
                ->withInput(['table' => $selectedTable])
                ->with('status', "Запись добавлена в «{$selectedTable}»");
        }
    }

    $rows  = collect();
    $total = 0;
    $pages = 1;

    if ($selectedTable) {
        $total = DB::table($selectedTable)->count();
        $pages = max((int) ceil($total / $limit), 1);
        $page  = min($page, $pages);

        $rows = DB::table($selectedTable)
            ->offset(($page - 1) * $limit)
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


Route::resource('houses', HouseController::class);



// Route::middleware(['auth'])->group(function () {
    
// });



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

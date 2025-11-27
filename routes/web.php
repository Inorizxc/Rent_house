<?php


use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HouseChatController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\UserController;
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
    Route::post('/houses/get-coordinates', 'getCoordinates')->name('houses.get-coordinates')->middleware('auth');
    Route::post('/houses/get-address-suggestions', 'getAddressSuggestions')->name('houses.get-address-suggestions')->middleware('auth');
});

Route::middleware(['auth', 'banned'])->group(function () {
    Route::controller(HouseChatController::class)->group(function () {
        Route::get('/house/{houseId}/chat', 'show')->name('house.chat');
        Route::post('/house/{houseId}/chat/message', 'sendMessage')->name('house.chat.send');
        Route::get('/house/{houseId}/chat/messages', 'getMessages')->name('house.chat.messages');
    });

    Route::controller(\App\Http\Controllers\HouseCalendarController::class)->group(function () {
        Route::post('/house/{houseId}/calendar/dates', 'updateDates')->name('house.calendar.update-dates');
        Route::post('/house/{houseId}/calendar/dates/range', 'updateDatesRange')->name('house.calendar.update-dates-range');
    });

    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders/{id}', 'show')->name('orders.show');
        Route::post('/house/{houseId}/order', 'createFromChat')->name('house.order.create');
        Route::get('/house/{houseId}/order/confirm', 'showConfirm')->name('house.order.confirm.show');
        Route::post('/house/{houseId}/order/confirm', 'confirm')->name('house.order.confirm');
        Route::post('/house/{houseId}/order/cancel', 'cancel')->name('house.order.cancel');
    });

    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/{chatId}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats/{chatId}/message', [ChatController::class, 'sendMessage'])->name('chats.send');
    Route::get('/chats/{chatId}/messages', [ChatController::class, 'getMessages'])->name('chats.messages');
    Route::get('/user/{userId}/chat', [ChatController::class, 'startWithUser'])->name('chats.start');
    Route::get('/chats/unread/count', [ChatController::class, 'getUnreadCount'])->name('chats.unread.count');
});

Route::prefix('profile/{id}')
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'show')->name('profile.show');
        Route::get('/houses', 'showHouses')->name('users.showHouses');
        Route::get('/tab/houses', 'tabHouses')->name('profile.tab.houses');
        Route::get('/tab/orders', 'tabOrders')->name('profile.tab.orders');
        Route::get('/tab/settings', 'tabSettings')->name('profile.tab.settings');
    });

Route::middleware(['auth', 'banned'])->group(function () {
    Route::post('/verification/request', [UserController::class, 'requestVerification'])->name('verification.request');
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

// Админ-панель
Route::middleware(['auth', 'admin'])->prefix('adminpanel')->name('admin.')->group(function () {
    Route::get('/', [AdminPanelController::class, 'index'])->name('panel');
    Route::post('/', [AdminPanelController::class, 'index'])->name('panel.store');
    Route::delete('/{table}/{id}', [AdminPanelController::class, 'delete'])->name('panel.delete');
    
    // Чаты
    Route::get('/chats', [AdminPanelController::class, 'chats'])->name('chats');
    Route::get('/chats/{chatId}', [AdminPanelController::class, 'chatShow'])->name('chat.show');
    
    // Заказы
    Route::get('/orders', [AdminPanelController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderId}', [AdminPanelController::class, 'orderShow'])->name('order.show');
    
    // Верификация
    Route::get('/verification', [\App\Http\Controllers\VerificationController::class, 'index'])->name('verification');
    Route::post('/verification/{userId}/approve', [\App\Http\Controllers\VerificationController::class, 'approve'])->name('verification.approve');
    Route::post('/verification/{userId}/reject', [\App\Http\Controllers\VerificationController::class, 'reject'])->name('verification.reject');
    
    // Баны
    Route::get('/bans', [\App\Http\Controllers\BanController::class, 'index'])->name('bans');
    Route::post('/bans/users/{userId}/ban', [\App\Http\Controllers\BanController::class, 'banUser'])->name('bans.user.ban');
    Route::post('/bans/users/{userId}/unban', [\App\Http\Controllers\BanController::class, 'unbanUser'])->name('bans.user.unban');
    Route::post('/bans/houses/{houseId}/ban', [\App\Http\Controllers\BanController::class, 'banHouse'])->name('bans.house.ban');
    Route::post('/bans/houses/{houseId}/unban', [\App\Http\Controllers\BanController::class, 'unbanHouse'])->name('bans.house.unban');
    Route::post('/bans/houses/{houseId}/delete', [\App\Http\Controllers\BanController::class, 'deleteHouse'])->name('bans.house.delete');
    Route::post('/bans/houses/{houseId}/restore', [\App\Http\Controllers\BanController::class, 'restoreHouse'])->name('bans.house.restore');
});

require __DIR__.'/auth.php';

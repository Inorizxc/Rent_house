<?php


use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\BanController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HouseCalendarController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HouseChatController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;


Route::controller(RouterController::class)->group(function () {
    Route::get('/map', 'map')->name('map');
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

    Route::controller(HouseCalendarController::class)->group(function () {
        Route::post('/house/{houseId}/calendar/dates', 'updateDates')->name('house.calendar.update-dates');
        Route::post('/house/{houseId}/calendar/dates/range', 'updateDatesRange')->name('house.calendar.update-dates-range');
    });

    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders/{id}', 'show')->name('orders.show');
        Route::post('/orders/{id}/approve', 'approve')->name('orders.approve');
        Route::post('/orders/{id}/refund/request', 'requestRefund')->name('orders.refund.request');
        Route::post('/orders/{id}/refund/approve', 'approveRefund')->name('orders.refund.approve');
        Route::post('/orders/{id}/order/payRest', 'payRest')->name('orders.order.payRest');
        Route::post('/house/{houseId}/order', 'createFromChat')->name('house.order.create');
        Route::get('/house/{houseId}/order/confirm', 'showConfirm')->name('house.order.confirm.show');
        Route::post('/house/{houseId}/order/confirm', 'confirm')->name('house.order.confirm');
        Route::post('/house/{houseId}/order/cancel', 'cancel')->name('house.order.cancel');
    });

    Route::controller(ChatController::class)->group(function () {
        Route::get('/chats', 'index')->name('chats.index');
        Route::get('/chats/{chatId}', 'show')->name('chats.show');
        Route::post('/chats/{chatId}/message', 'sendMessage')->name('chats.send');
        Route::get('/chats/{chatId}/messages', 'getMessages')->name('chats.messages');
        Route::get('/user/{userId}/chat', 'startWithUser')->name('chats.start');
        Route::get('/chats/unread/count', 'getUnreadCount')->name('chats.unread.count');
    });
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
    Route::controller(UserController::class)->group(function () {
        Route::post('/verification/request', 'requestVerification')->name('verification.request');
    });
});


Route::get('/', function () {
    return redirect()->route('map');
})->name('home');

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
    Route::controller(AdminPanelController::class)->group(function () {
        Route::get('/', 'index')->name('panel');
        Route::post('/', 'index')->name('panel.store');
        Route::delete('/{table}/{id}', 'delete')->name('panel.delete');
        
        // Чаты
        Route::get('/chats', 'chats')->name('chats');
        Route::get('/chats/{chatId}', 'chatShow')->name('chat.show');
        
        // Заказы
        Route::get('/orders', 'orders')->name('orders');
        Route::get('/orders/{orderId}', 'orderShow')->name('order.show');
        Route::post('/orders/{orderId}/refund', 'refundOrder')->name('orders.refund');
    });
    
    // Верификация
    Route::controller(VerificationController::class)->group(function () {
        Route::get('/verification', 'index')->name('verification');
        Route::post('/verification/{userId}/approve', 'approve')->name('verification.approve');
        Route::post('/verification/{userId}/reject', 'reject')->name('verification.reject');
    });
    
    // Баны
    Route::controller(BanController::class)->group(function () {
        Route::get('/bans', 'index')->name('bans');
        Route::post('/bans/users/{userId}/ban', 'banUser')->name('bans.user.ban');
        Route::post('/bans/users/{userId}/unban', 'unbanUser')->name('bans.user.unban');
        Route::post('/bans/houses/{houseId}/ban', 'banHouse')->name('bans.house.ban');
        Route::post('/bans/houses/{houseId}/unban', 'unbanHouse')->name('bans.house.unban');
        Route::post('/bans/houses/{houseId}/delete', 'deleteHouse')->name('bans.house.delete');
        Route::post('/bans/houses/{houseId}/restore', 'restoreHouse')->name('bans.house.restore');
    });
});

require __DIR__.'/auth.php';

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Models\User;
use App\Observers\UserBanObserver;
class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('ru');
        date_default_timezone_set('Europe/Moscow');
        Order::observe(OrderObserver::class);
        User::observe(UserBanObserver::class);
    }
}

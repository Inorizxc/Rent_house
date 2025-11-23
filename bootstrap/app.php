<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use function Clue\StreamFilter\append;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'isuser' => App\Http\Middleware\UserCheck::class,
            'isAdmin' => App\Http\Middleware\CheckAdmin::class,
            'isHouseOwner' => App\Http\Middleware\HouseOwnerCheck::class,
        ]);
        $middleware->web([
            
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

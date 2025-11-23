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
            'user' => app\Http\Middleware\UserCheck::class
        ]);
        $middleware->web([
            'user'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

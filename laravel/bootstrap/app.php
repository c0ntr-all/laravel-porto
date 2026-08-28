<?php

use App\Ship\Exceptions\Handlers\ConfigureExceptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../app/Ship/Broadcasts/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Ship\Middleware\InitCorrelationMiddleware::class,
            \App\Ship\Middleware\AppendCorrelationUuidMiddleware::class,
        ]);
    })
    ->withExceptions(new ConfigureExceptions)
    ->create();

<?php
use App\Containers\DashboardSection\Widget\UI\CLI\Commands\MakeWidgetCommand;
use App\Containers\TaskManagerSection\Reminder\UI\CLI\Commands\ProcessDueRemindersCommand;
use App\Ship\Middleware\AuthenticateBearerFromQuery;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../app/Ship/Broadcasts/channels.php',
        health: '/up',
    )
    ->withCommands([
        ProcessDueRemindersCommand::class,
        MakeWidgetCommand::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(ProcessDueRemindersCommand::class)
            ->everyMinute()
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Ship\Middleware\InitCorrelationMiddleware::class,
            \App\Ship\Middleware\AppendCorrelationUuidMiddleware::class,
        ]);

        // <audio>/<img> send access_token as a query param. Laravel's middleware
        // priority otherwise moves auth:api ahead of the route middleware that
        // copies it onto Authorization, so a valid token still looks anonymous.
        $middleware->prependToGroup('api', AuthenticateBearerFromQuery::class);
        $middleware->prependToPriorityList(
            AuthenticatesRequests::class,
            AuthenticateBearerFromQuery::class,
        );

        // This app has no named "login" route. Media requests send Accept: audio/*
        // (not JSON), and the default redirectGuestsTo(route('login')) becomes a 500.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();

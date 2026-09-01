<?php
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Containers\DashboardSection\Widget\UI\CLI\Commands\MakeWidgetCommand;
use App\Containers\TaskManagerSection\Reminder\UI\CLI\Commands\ProcessDueRemindersCommand;

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
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

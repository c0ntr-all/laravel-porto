<?php declare(strict_types=1);

use App\Containers\AppSection\ActivityLog\UI\Actions\ListUseCaseLogsAction;
use Illuminate\Support\Facades\Route;

Route::get('use-case-logs', ListUseCaseLogsAction::class)
     ->middleware(['auth:api']);

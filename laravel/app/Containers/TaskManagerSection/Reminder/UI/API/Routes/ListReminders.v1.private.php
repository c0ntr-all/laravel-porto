<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Reminder\UI\Actions\ListRemindersAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/reminders', ListRemindersAction::class)
     ->middleware(['auth:api']);

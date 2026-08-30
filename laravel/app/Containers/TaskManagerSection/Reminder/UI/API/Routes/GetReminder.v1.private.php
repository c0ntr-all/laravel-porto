<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Reminder\UI\Actions\GetReminderAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/tasks/{task}/reminder', GetReminderAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Reminder\UI\Actions\DeleteReminderAction;
use Illuminate\Support\Facades\Route;

Route::delete('task-manager/tasks/{task}/reminder', DeleteReminderAction::class)
     ->middleware(['auth:api']);

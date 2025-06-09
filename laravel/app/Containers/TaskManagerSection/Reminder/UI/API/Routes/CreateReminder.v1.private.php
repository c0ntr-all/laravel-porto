<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Reminder\UI\Actions\CreateReminderAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/tasks/{task}/reminder', CreateReminderAction::class)
     ->middleware(['auth:api']);

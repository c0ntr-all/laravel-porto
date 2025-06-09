<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Reminder\UI\Actions\UpdateReminderAction;
use Illuminate\Support\Facades\Route;

Route::patch('task-manager/tasks/{task}/reminder/{reminder}', UpdateReminderAction::class)
     ->middleware(['auth:api']);

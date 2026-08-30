<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Reminder\UI\Actions\CreateReminderOccurrenceAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/tasks/{task}/reminder/occurrences', CreateReminderOccurrenceAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Reminder\UI\Actions\ListReminderOccurrencesAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/tasks/{task}/reminder/occurrences', ListReminderOccurrencesAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Checklist\UI\Actions\ConvertChecklistItemToTaskAction;
use Illuminate\Support\Facades\Route;

Route::post(
    'task-manager/tasks/{task}/checklists/{checklist}/items/{checklistItem}/convert-to-task',
    ConvertChecklistItemToTaskAction::class
)->middleware(['auth:api']);

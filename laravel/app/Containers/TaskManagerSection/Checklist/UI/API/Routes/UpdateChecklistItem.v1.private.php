<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Checklist\UI\Actions\UpdateChecklistItemAction;
use Illuminate\Support\Facades\Route;

Route::patch('task-manager/tasks/{task}/checklists/{checklist}/items/{checklistItem}', UpdateChecklistItemAction::class)
     ->middleware(['auth:api']);

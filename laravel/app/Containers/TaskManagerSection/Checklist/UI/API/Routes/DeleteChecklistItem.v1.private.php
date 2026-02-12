<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Checklist\UI\Actions\DeleteChecklistItemAction;
use Illuminate\Support\Facades\Route;

Route::delete('task-manager/tasks/{task}/checklists/{checklist}/items/{checklistItem}', DeleteChecklistItemAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Checklist\UI\Actions\CreateChecklistItemAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/tasks/{task}/checklists/{checklist}/items', CreateChecklistItemAction::class)
     ->middleware(['auth:api']);

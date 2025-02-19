<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Checklist\UI\Actions\CreateChecklistAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/tasks/{task}/checklists', CreateChecklistAction::class)
     ->middleware(['auth:api']);

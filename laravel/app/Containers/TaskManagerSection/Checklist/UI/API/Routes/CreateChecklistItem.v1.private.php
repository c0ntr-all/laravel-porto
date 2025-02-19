<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Task\UI\Actions\CreateTaskAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/tasks/{task}/checklists/{checklist}/item', CreateTaskAction::class)
     ->middleware(['auth:api']);

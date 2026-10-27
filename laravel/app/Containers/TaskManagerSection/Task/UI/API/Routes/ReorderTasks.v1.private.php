<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Task\UI\Actions\ReorderTasksAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/tasks/reorder', ReorderTasksAction::class)
     ->middleware(['auth:api']);

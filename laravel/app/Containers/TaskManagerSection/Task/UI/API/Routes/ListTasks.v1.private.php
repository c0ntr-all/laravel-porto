<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Task\UI\Actions\ListTasksAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/tasks', ListTasksAction::class)
     ->middleware(['auth:api']);

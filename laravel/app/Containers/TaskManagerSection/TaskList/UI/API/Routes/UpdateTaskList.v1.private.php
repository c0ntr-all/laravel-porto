<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskList\UI\Actions\UpdateTaskListAction;
use Illuminate\Support\Facades\Route;

Route::patch('task-manager/task-lists/{taskList}', UpdateTaskListAction::class)
     ->middleware(['auth:api']);

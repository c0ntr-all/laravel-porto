<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskList\UI\Actions\DeleteTaskListAction;
use Illuminate\Support\Facades\Route;

Route::delete('task-manager/task-lists/{taskList}', DeleteTaskListAction::class)
     ->middleware(['auth:api']);

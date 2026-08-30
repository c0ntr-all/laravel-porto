<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskList\UI\Actions\GetTaskListAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/task-lists/{taskList}', GetTaskListAction::class)
     ->middleware(['auth:api']);

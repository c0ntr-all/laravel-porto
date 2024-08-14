<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskList\UI\Actions\CreateTaskListAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/task-lists', CreateTaskListAction::class)
     ->middleware(['auth:api']);

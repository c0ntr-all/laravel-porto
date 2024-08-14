<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Task\UI\Actions\DeleteTaskAction;
use Illuminate\Support\Facades\Route;

Route::delete('task-manager/tasks/{task}', DeleteTaskAction::class)
     ->middleware(['auth:api']);

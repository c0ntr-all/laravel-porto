<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Task\UI\Actions\UpdateTaskAction;
use Illuminate\Support\Facades\Route;

Route::patch('task-manager/tasks/{task}', UpdateTaskAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskProgress\UI\Actions\UpdateTaskProgressAction;
use Illuminate\Support\Facades\Route;

Route::patch('task-manager/tasks/{task}/progress/{progress}', UpdateTaskProgressAction::class)
     ->middleware(['auth:api']);

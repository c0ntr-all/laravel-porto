<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskProgress\UI\Actions\CreateTaskProgressAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/tasks/{task}/progress', CreateTaskProgressAction::class)
     ->middleware(['auth:api']);

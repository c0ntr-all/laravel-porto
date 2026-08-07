<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskTemplate\UI\Actions\UpdateTaskTemplateAction;
use Illuminate\Support\Facades\Route;

Route::patch('task-manager/task-templates/{taskTemplate}', UpdateTaskTemplateAction::class)
     ->middleware(['auth:api']);

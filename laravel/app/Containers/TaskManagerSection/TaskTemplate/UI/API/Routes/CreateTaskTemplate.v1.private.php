<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskTemplate\UI\Actions\CreateTaskTemplateAction;
use Illuminate\Support\Facades\Route;

Route::post('task-manager/task-templates', CreateTaskTemplateAction::class)
     ->middleware(['auth:api']);

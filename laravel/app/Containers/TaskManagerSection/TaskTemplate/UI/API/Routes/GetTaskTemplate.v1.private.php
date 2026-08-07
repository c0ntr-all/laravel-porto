<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskTemplate\UI\Actions\GetTaskTemplateAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/task-templates/{taskTemplate}', GetTaskTemplateAction::class)
     ->middleware(['auth:api']);

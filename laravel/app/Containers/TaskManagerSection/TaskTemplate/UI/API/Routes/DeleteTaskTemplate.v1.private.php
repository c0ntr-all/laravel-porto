<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskTemplate\UI\Actions\DeleteTaskTemplateAction;
use Illuminate\Support\Facades\Route;

Route::delete('task-manager/task-templates/{taskTemplate}', DeleteTaskTemplateAction::class)
     ->middleware(['auth:api']);

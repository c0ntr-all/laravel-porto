<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskTemplate\UI\Actions\ListTaskTemplatesAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/task-templates', ListTaskTemplatesAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\TaskList\UI\Actions\ListTaskListsAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/task-lists', ListTaskListsAction::class)
     ->middleware(['auth:api']);

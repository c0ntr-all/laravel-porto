<?php declare(strict_types=1);

use App\Containers\MusicSection\Album\UI\Actions\GetAlbumAction;
use App\Containers\TaskManagerSection\Task\UI\Actions\GetTaskAction;
use Illuminate\Support\Facades\Route;

Route::get('task-manager/tasks/{task}', GetTaskAction::class)
     ->middleware(['auth:api']);

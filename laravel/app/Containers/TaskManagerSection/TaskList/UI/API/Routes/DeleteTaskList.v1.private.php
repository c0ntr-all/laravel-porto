<?php declare(strict_types=1);

use App\Containers\MusicSection\Album\UI\Actions\GetAlbumAction;
use Illuminate\Support\Facades\Route;

Route::delete('task-manager/task-lists/{taskList}', GetAlbumAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\MovieSection\Folder\UI\Actions\AttachMovieToFolderAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/folders/{folder}/movies', AttachMovieToFolderAction::class)
    ->middleware(['auth:api']);

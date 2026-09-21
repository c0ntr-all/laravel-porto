<?php declare(strict_types=1);

use App\Containers\MovieSection\Folder\UI\Actions\DetachMovieFromFolderAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/folders/{folder}/movies/{movie}', DetachMovieFromFolderAction::class)
    ->middleware(['auth:api']);

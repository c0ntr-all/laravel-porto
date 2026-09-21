<?php declare(strict_types=1);

use App\Containers\MovieSection\Folder\UI\Actions\ListFolderMoviesAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/folders/{folder}/movies', ListFolderMoviesAction::class)
    ->middleware(['auth:api']);

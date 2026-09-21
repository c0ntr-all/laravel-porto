<?php declare(strict_types=1);

use App\Containers\MovieSection\Folder\UI\Actions\ListFoldersAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/folders', ListFoldersAction::class)
    ->middleware(['auth:api']);

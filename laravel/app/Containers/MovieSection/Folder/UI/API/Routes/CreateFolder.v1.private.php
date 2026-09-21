<?php declare(strict_types=1);

use App\Containers\MovieSection\Folder\UI\Actions\CreateFolderAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/folders', CreateFolderAction::class)
    ->middleware(['auth:api']);

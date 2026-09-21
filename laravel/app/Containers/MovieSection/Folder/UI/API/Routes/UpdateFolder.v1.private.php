<?php declare(strict_types=1);

use App\Containers\MovieSection\Folder\UI\Actions\UpdateFolderAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/folders/{folder}', UpdateFolderAction::class)
    ->middleware(['auth:api']);

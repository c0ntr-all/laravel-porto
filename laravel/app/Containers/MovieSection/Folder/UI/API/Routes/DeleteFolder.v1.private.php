<?php declare(strict_types=1);

use App\Containers\MovieSection\Folder\UI\Actions\DeleteFolderAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/folders/{folder}', DeleteFolderAction::class)
    ->middleware(['auth:api']);

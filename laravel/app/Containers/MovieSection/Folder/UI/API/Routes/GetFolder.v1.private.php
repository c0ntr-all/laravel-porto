<?php declare(strict_types=1);

use App\Containers\MovieSection\Folder\UI\Actions\GetFolderAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/folders/{folder}', GetFolderAction::class)
    ->middleware(['auth:api']);

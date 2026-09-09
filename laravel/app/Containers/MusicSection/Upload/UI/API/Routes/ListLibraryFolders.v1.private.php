<?php declare(strict_types=1);

use App\Containers\MusicSection\Upload\UI\Actions\ListLibraryFoldersAction;
use Illuminate\Support\Facades\Route;

Route::get('music/library/folders', ListLibraryFoldersAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\MusicSection\Album\UI\Actions\DeleteAlbumAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/albums/{album}', DeleteAlbumAction::class)
     ->middleware(['auth:api']);

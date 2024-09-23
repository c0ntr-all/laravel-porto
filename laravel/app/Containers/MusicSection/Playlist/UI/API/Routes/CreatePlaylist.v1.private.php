<?php declare(strict_types=1);

use App\Containers\MusicSection\Playlist\UI\Actions\CreatePlaylistAction;
use Illuminate\Support\Facades\Route;

Route::post('music/playlists', CreatePlaylistAction::class)
     ->middleware(['auth:api']);

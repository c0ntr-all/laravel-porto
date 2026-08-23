<?php declare(strict_types=1);

use App\Containers\MusicSection\Playlist\UI\Actions\DeletePlaylistAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/playlists/{playlist}', DeletePlaylistAction::class)
     ->middleware(['auth:api']);

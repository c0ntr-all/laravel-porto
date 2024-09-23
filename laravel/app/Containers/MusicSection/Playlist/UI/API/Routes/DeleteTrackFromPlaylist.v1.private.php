<?php declare(strict_types=1);

use App\Containers\MusicSection\Playlist\UI\Actions\DeleteTrackFromPlaylistAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/playlists/{playlist}/tracks/{track}', DeleteTrackFromPlaylistAction::class)
     ->middleware(['auth:api']);

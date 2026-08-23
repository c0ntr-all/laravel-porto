<?php declare(strict_types=1);

use App\Containers\MusicSection\Playlist\UI\Actions\UpdatePlaylistAction;
use Illuminate\Support\Facades\Route;

Route::patch('music/playlists/{playlist}', UpdatePlaylistAction::class)
     ->middleware(['auth:api']);

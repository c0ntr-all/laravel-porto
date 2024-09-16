<?php declare(strict_types=1);

use App\Containers\MusicSection\Track\UI\Actions\AddTrackToPlaylistsAction;
use Illuminate\Support\Facades\Route;

Route::put('music/tracks/{track}/playlists', AddTrackToPlaylistsAction::class)
     ->middleware(['auth:api']);

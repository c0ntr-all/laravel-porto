<?php declare(strict_types=1);

use App\Containers\MusicSection\Playlist\UI\Actions\ListPlaylistsAction;
use Illuminate\Support\Facades\Route;

Route::get('music/playlists', ListPlaylistsAction::class)
     ->middleware(['auth:api']);

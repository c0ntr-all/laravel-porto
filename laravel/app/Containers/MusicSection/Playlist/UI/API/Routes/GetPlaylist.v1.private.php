<?php declare(strict_types=1);

use App\Containers\MusicSection\Playlist\UI\Actions\GetPlaylistAction;
use Illuminate\Support\Facades\Route;

Route::get('music/playlists/{playlist}', GetPlaylistAction::class)
     ->middleware(['auth:api']);

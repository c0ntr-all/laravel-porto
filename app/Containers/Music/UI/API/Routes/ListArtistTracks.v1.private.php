<?php declare(strict_types=1);

use App\Containers\Music\UI\Actions\Tracks\ListTracksByArtistAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists/{artist}/tracks', ListTracksByArtistAction::class)
     ->middleware(['auth:api']);

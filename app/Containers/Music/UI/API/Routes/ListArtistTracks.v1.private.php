<?php

use App\Containers\Music\UI\Actions\ListArtistTracksAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists/{artist}/tracks', ListArtistTracksAction::class)
    ->middleware(['auth:api']);

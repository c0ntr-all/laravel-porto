<?php

use App\Containers\Music\UI\Actions\ListArtistAlbumsAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists/{artist}/albums', ListArtistAlbumsAction::class)
    ->middleware(['auth:api']);

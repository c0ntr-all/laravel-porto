<?php

use App\Containers\Music\UI\Actions\GetArtistAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists/{artist}', GetArtistAction::class)
    ->middleware(['auth:api']);

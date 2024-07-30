<?php declare(strict_types=1);

use App\Containers\MusicSection\Artist\UI\Actions\ListTracksByArtistAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists/{artist}/tracks', ListTracksByArtistAction::class)
     ->middleware(['auth:api']);

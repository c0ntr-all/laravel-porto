<?php declare(strict_types=1);

use App\Containers\MusicSection\Track\UI\Actions\ListTracksAction;
use Illuminate\Support\Facades\Route;

Route::get('music/tracks', ListTracksAction::class)
     ->middleware(['auth:api']);

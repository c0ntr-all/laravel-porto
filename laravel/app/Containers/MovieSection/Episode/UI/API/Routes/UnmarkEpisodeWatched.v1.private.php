<?php declare(strict_types=1);

use App\Containers\MovieSection\Episode\UI\Actions\UnmarkEpisodeWatchedAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/episodes/{episode}/watch', UnmarkEpisodeWatchedAction::class)
    ->middleware(['auth:api']);

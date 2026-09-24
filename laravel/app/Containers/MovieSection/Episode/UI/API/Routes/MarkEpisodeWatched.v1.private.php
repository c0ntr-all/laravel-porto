<?php declare(strict_types=1);

use App\Containers\MovieSection\Episode\UI\Actions\MarkEpisodeWatchedAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/episodes/{episode}/watch', MarkEpisodeWatchedAction::class)
    ->middleware(['auth:api']);

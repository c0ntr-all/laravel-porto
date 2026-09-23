<?php declare(strict_types=1);

use App\Containers\MovieSection\Episode\UI\Actions\DeleteEpisodeAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/episodes/{episode}', DeleteEpisodeAction::class)
    ->middleware(['auth:api']);

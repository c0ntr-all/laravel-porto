<?php declare(strict_types=1);

use App\Containers\MovieSection\Episode\UI\Actions\GetEpisodeAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/episodes/{episode}', GetEpisodeAction::class)
    ->middleware(['auth:api']);

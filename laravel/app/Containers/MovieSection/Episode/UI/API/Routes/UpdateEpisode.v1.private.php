<?php declare(strict_types=1);

use App\Containers\MovieSection\Episode\UI\Actions\UpdateEpisodeAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/episodes/{episode}', UpdateEpisodeAction::class)
    ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\MovieSection\Episode\UI\Actions\CreateEpisodeAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/episodes', CreateEpisodeAction::class)
    ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\MovieSection\Episode\UI\Actions\ListEpisodesAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/episodes', ListEpisodesAction::class)
    ->middleware(['auth:api']);

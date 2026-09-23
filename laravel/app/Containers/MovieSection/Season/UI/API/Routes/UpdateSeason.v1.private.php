<?php declare(strict_types=1);

use App\Containers\MovieSection\Season\UI\Actions\UpdateSeasonAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/seasons/{season}', UpdateSeasonAction::class)
    ->middleware(['auth:api']);

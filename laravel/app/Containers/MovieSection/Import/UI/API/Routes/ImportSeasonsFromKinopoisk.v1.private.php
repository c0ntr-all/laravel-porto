<?php declare(strict_types=1);

use App\Containers\MovieSection\Import\UI\Actions\ImportSeasonsFromKinopoiskAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/imports/seasons', ImportSeasonsFromKinopoiskAction::class)
    ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\MovieSection\Season\UI\Actions\ListSeasonsAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/seasons', ListSeasonsAction::class)
    ->middleware(['auth:api']);

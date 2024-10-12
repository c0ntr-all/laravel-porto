<?php declare(strict_types=1);

use App\Containers\MusicSection\Track\UI\Actions\RateTrackAction;
use Illuminate\Support\Facades\Route;

Route::post('music/tracks/{track}/rate', RateTrackAction::class)
     ->middleware(['auth:api']);

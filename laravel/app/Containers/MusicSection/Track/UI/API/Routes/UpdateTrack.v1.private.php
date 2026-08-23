<?php declare(strict_types=1);

use App\Containers\MusicSection\Track\UI\Actions\UpdateTrackAction;
use Illuminate\Support\Facades\Route;

Route::patch('music/tracks/{track}', UpdateTrackAction::class)
     ->middleware(['auth:api']);

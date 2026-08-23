<?php declare(strict_types=1);

use App\Containers\MusicSection\Track\UI\Actions\CreateTrackAction;
use Illuminate\Support\Facades\Route;

Route::post('music/tracks', CreateTrackAction::class)
     ->middleware(['auth:api']);

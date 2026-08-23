<?php declare(strict_types=1);

use App\Containers\MusicSection\Track\UI\Actions\DeleteTrackAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/tracks/{track}', DeleteTrackAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\MusicSection\Track\UI\Actions\GetTrackAction;
use Illuminate\Support\Facades\Route;

Route::get('music/tracks/{track}', GetTrackAction::class)
     ->middleware(['auth:api']);

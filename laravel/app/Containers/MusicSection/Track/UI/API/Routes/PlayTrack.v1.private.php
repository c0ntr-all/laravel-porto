<?php declare(strict_types=1);

use App\Containers\MusicSection\Track\UI\Actions\PlayTrackAction;
use App\Ship\Middleware\AuthenticateBearerFromQuery;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'HEAD'], 'music/tracks/{track}/play', PlayTrackAction::class)
     ->middleware([AuthenticateBearerFromQuery::class, 'auth:api'])
     ->withoutMiddleware('throttle:api');

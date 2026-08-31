<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\GetVideoFileAction;
use App\Ship\Middleware\AuthenticateBearerFromQuery;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'HEAD'], 'gallery/videos/{video}/file', GetVideoFileAction::class)
     ->middleware([AuthenticateBearerFromQuery::class, 'auth:api'])
     ->withoutMiddleware('throttle:api');

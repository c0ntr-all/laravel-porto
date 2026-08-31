<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\GetImageFileAction;
use App\Ship\Middleware\AuthenticateBearerFromQuery;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'HEAD'], 'gallery/images/{image}/file', GetImageFileAction::class)
     ->middleware([AuthenticateBearerFromQuery::class, 'auth:api'])
     ->withoutMiddleware('throttle:api');

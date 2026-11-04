<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\QueueComputerThumbnailsAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/library/thumbnails', QueueComputerThumbnailsAction::class)
    ->middleware(['auth:api']);

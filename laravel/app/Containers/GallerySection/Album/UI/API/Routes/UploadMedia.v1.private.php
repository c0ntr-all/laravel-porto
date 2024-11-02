<?php declare(strict_types=1);

use App\Containers\GallerySection\Album\UI\Actions\UploadMediaToAlbumAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/media', UploadMediaToAlbumAction::class)
     ->middleware(['auth:api']);

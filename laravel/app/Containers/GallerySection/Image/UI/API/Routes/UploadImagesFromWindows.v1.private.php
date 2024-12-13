<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\UploadImagesFromWindowsAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/images/upload-windows', UploadImagesFromWindowsAction::class)
     ->middleware(['auth:api']);

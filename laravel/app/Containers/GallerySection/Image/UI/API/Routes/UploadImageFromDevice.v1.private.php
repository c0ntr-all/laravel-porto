<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\UploadImageFromDeviceAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/images/upload', UploadImageFromDeviceAction::class)
     ->middleware(['auth:api']);

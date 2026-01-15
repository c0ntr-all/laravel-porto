<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\UploadVideoFromDeviceAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/videos/upload', UploadVideoFromDeviceAction::class)
     ->middleware(['auth:api']);

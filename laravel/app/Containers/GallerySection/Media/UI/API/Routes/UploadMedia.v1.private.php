<?php declare(strict_types=1);

use App\Containers\GallerySection\Media\UI\Actions\UploadMediaFromDeviceAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/media/upload', UploadMediaFromDeviceAction::class)
     ->middleware(['auth:api']);

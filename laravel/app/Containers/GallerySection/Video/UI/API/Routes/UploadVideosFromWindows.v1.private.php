<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\UploadVideosFromWindowsAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/videos/upload-windows', UploadVideosFromWindowsAction::class)
     ->middleware(['auth:api']);

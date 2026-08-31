<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\UploadVideoFromWebAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/videos/upload-web', UploadVideoFromWebAction::class)
     ->middleware(['auth:api']);

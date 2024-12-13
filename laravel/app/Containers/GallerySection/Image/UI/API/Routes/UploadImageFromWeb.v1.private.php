<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\UploadImageFromWebAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/images/upload-web', UploadImageFromWebAction::class)
     ->middleware(['auth:api']);

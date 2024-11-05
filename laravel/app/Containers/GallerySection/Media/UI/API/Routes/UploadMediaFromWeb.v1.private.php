<?php declare(strict_types=1);

use App\Containers\GallerySection\Media\UI\Actions\UploadMediaFromWebAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/media/upload-web', UploadMediaFromWebAction::class)
     ->middleware(['auth:api']);

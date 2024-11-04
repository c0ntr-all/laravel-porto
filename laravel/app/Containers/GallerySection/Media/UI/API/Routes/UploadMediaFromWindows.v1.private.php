<?php declare(strict_types=1);

use App\Containers\GallerySection\Media\UI\Actions\UploadMediaFromWindowsAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/media/upload-windows', UploadMediaFromWindowsAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\GallerySection\Media\UI\Actions\UploadMediaAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums/{album}/media/upload', UploadMediaAction::class)
     ->middleware(['auth:api']);

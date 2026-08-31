<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\ListImagesAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/albums/{album}/images', ListImagesAction::class)
     ->middleware(['auth:api']);

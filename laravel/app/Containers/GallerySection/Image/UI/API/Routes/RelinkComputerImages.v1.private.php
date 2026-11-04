<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\RelinkComputerImagesAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/library/relink', RelinkComputerImagesAction::class)
    ->middleware(['auth:api']);

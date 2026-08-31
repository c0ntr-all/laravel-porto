<?php declare(strict_types=1);

use App\Containers\GallerySection\Album\UI\Actions\CreateAlbumAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/albums', CreateAlbumAction::class)
     ->middleware(['auth:api']);

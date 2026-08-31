<?php declare(strict_types=1);

use App\Containers\GallerySection\Album\UI\Actions\DeleteAlbumAction;
use Illuminate\Support\Facades\Route;

Route::delete('gallery/albums/{album}', DeleteAlbumAction::class)
     ->middleware(['auth:api']);

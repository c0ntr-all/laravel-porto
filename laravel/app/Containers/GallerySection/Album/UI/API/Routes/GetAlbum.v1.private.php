<?php declare(strict_types=1);

use App\Containers\GallerySection\Album\UI\Actions\GetAlbumAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/albums/{album}', GetAlbumAction::class)
     ->middleware(['auth:api']);

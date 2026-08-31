<?php declare(strict_types=1);

use App\Containers\GallerySection\Album\UI\Actions\UpdateAlbumAction;
use Illuminate\Support\Facades\Route;

Route::patch('gallery/albums/{album}', UpdateAlbumAction::class)
     ->middleware(['auth:api']);

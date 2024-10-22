<?php declare(strict_types=1);

use App\Containers\GallerySection\Album\UI\Actions\ListAlbumsAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/albums', ListAlbumsAction::class)
     ->middleware(['auth:api']);

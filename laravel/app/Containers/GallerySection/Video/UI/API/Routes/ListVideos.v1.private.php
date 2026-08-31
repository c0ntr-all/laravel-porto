<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\ListVideosAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/albums/{album}/videos', ListVideosAction::class)
     ->middleware(['auth:api']);

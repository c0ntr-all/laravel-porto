<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\SyncImageTagsAction;
use Illuminate\Support\Facades\Route;

Route::put('gallery/images/{image}/tags', SyncImageTagsAction::class)
     ->middleware(['auth:api']);

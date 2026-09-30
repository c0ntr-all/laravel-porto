<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\SyncVideoTagsAction;
use Illuminate\Support\Facades\Route;

Route::put('gallery/videos/{video}/tags', SyncVideoTagsAction::class)
     ->middleware(['auth:api']);

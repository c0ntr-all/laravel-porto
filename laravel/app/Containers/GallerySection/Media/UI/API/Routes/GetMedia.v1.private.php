<?php declare(strict_types=1);

use App\Containers\GallerySection\Media\UI\Actions\GetMediaAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/media/{media}', GetMediaAction::class)
     ->middleware(['auth:api']);

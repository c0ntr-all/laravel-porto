<?php declare(strict_types=1);

use App\Containers\GallerySection\Photo\UI\Actions\GetPhotoAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/photos/{photo}', GetPhotoAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\DeleteImageAction;
use Illuminate\Support\Facades\Route;

Route::delete('gallery/images/{image}', DeleteImageAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\UpdateImageAction;
use Illuminate\Support\Facades\Route;

Route::patch('gallery/images/{image}', UpdateImageAction::class)
     ->middleware(['auth:api']);

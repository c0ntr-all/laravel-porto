<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\GetImageAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/images/{image}', GetImageAction::class)
     ->middleware(['auth:api']);

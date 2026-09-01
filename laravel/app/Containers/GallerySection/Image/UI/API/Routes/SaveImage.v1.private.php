<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\SaveImageAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/images/{image}/save', SaveImageAction::class)
     ->middleware(['auth:api']);

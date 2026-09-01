<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\SaveVideoAction;
use Illuminate\Support\Facades\Route;

Route::post('gallery/videos/{video}/save', SaveVideoAction::class)
     ->middleware(['auth:api']);

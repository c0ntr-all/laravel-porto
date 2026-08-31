<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\GetVideoAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/videos/{video}', GetVideoAction::class)
     ->middleware(['auth:api']);

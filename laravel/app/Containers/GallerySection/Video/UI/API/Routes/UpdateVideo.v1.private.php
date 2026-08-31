<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\UpdateVideoAction;
use Illuminate\Support\Facades\Route;

Route::patch('gallery/videos/{video}', UpdateVideoAction::class)
     ->middleware(['auth:api']);

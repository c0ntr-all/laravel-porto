<?php declare(strict_types=1);

use App\Containers\GallerySection\Video\UI\Actions\DeleteVideoAction;
use Illuminate\Support\Facades\Route;

Route::delete('gallery/videos/{video}', DeleteVideoAction::class)
     ->middleware(['auth:api']);

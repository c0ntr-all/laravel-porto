<?php declare(strict_types=1);

use App\Containers\LifelogSection\Post\UI\Actions\CreatePostAction;
use Illuminate\Support\Facades\Route;

Route::post('lifelog/posts', CreatePostAction::class)
     ->middleware(['auth:api']);

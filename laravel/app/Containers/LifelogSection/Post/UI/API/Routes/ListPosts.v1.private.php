<?php declare(strict_types=1);

use App\Containers\LifelogSection\Post\UI\Actions\ListPostsAction;
use Illuminate\Support\Facades\Route;

Route::get('lifelog/posts', ListPostsAction::class)
     ->middleware(['auth:api']);

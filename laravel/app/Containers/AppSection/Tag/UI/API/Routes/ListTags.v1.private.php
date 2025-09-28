<?php declare(strict_types=1);

use App\Containers\AppSection\Tag\UI\Actions\ListTagsAction;
use Illuminate\Support\Facades\Route;

Route::get('tags', ListTagsAction::class)
     ->middleware(['auth:api']);

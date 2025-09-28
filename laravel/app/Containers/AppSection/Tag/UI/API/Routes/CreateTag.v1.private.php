<?php declare(strict_types=1);

use App\Containers\AppSection\Tag\UI\Actions\CreateTagAction;
use Illuminate\Support\Facades\Route;

Route::post('tags', CreateTagAction::class)
     ->middleware(['auth:api']);

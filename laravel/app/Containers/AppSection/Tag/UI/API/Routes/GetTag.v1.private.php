<?php declare(strict_types=1);

use App\Containers\AppSection\Tag\UI\Actions\GetTagAction;
use Illuminate\Support\Facades\Route;

Route::get('tags/{tag:slug}', GetTagAction::class)
     ->middleware(['auth:api']);

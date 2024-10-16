<?php declare(strict_types=1);

use App\Containers\AppSection\Comment\UI\Actions\CreateCommentAction;
use Illuminate\Support\Facades\Route;

Route::post('comments', CreateCommentAction::class)
    ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\AppSection\Comment\UI\Actions\DeleteCommentAction;
use Illuminate\Support\Facades\Route;

Route::delete('comments/{comment}', DeleteCommentAction::class)
    ->middleware(['auth:api']);

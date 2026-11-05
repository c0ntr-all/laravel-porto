<?php declare(strict_types=1);

use App\Containers\AppSection\Comment\UI\Actions\UpdateCommentAction;
use Illuminate\Support\Facades\Route;

Route::patch('comments/{comment}', UpdateCommentAction::class)
    ->middleware(['auth:api']);

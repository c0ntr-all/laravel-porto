<?php declare(strict_types=1);

use App\Containers\AppSection\Comment\UI\Actions\ListCommentsAction;
use Illuminate\Support\Facades\Route;

Route::get('comments', ListCommentsAction::class)
     ->middleware(['auth:api']);

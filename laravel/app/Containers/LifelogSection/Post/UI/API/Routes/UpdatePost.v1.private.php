<?php declare(strict_types=1);

use App\Containers\LifelogSection\Post\UI\Actions\UpdatePostAction;
use Illuminate\Support\Facades\Route;

Route::patch('lifelog/posts/{post}', UpdatePostAction::class)
     ->middleware(['auth:api']);

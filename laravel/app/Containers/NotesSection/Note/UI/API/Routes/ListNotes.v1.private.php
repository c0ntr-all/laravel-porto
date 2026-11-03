<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\UI\Actions\ListNotesAction;
use Illuminate\Support\Facades\Route;

Route::get('notes', ListNotesAction::class)
    ->middleware(['auth:api']);

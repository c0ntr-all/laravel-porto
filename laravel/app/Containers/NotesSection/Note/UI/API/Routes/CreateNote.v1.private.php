<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\UI\Actions\CreateNoteAction;
use Illuminate\Support\Facades\Route;

Route::post('notes', CreateNoteAction::class)
    ->middleware(['auth:api']);

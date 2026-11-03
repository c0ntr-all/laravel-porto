<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\UI\Actions\UpdateNoteAction;
use Illuminate\Support\Facades\Route;

Route::patch('notes/{note}', UpdateNoteAction::class)
    ->middleware(['auth:api']);

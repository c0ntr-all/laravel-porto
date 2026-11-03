<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\UI\Actions\GetNoteAction;
use Illuminate\Support\Facades\Route;

Route::get('notes/{note}', GetNoteAction::class)
    ->middleware(['auth:api']);

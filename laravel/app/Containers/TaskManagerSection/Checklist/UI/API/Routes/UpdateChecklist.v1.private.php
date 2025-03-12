<?php declare(strict_types=1);

use App\Containers\TaskManagerSection\Checklist\UI\Actions\UpdateChecklistAction;
use Illuminate\Support\Facades\Route;

Route::patch('task-manager/tasks/{task}/checklists/{checklist}', UpdateChecklistAction::class)
     ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\AppSection\CustomField\UI\Actions\UpdateCustomFieldAction;
use Illuminate\Support\Facades\Route;

Route::patch('app/custom-fields/{customField}', UpdateCustomFieldAction::class)
    ->middleware(['auth:api']);

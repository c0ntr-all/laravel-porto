<?php declare(strict_types=1);

use App\Containers\AppSection\CustomField\UI\Actions\CreateCustomFieldAction;
use Illuminate\Support\Facades\Route;

Route::post('app/custom-fields', CreateCustomFieldAction::class)
    ->middleware(['auth:api']);

<?php declare(strict_types=1);

use App\Containers\AppSection\CustomField\UI\Actions\ListCustomFieldsAction;
use Illuminate\Support\Facades\Route;

Route::get('app/custom-fields', ListCustomFieldsAction::class)
    ->middleware(['auth:api']);

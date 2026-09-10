<?php declare(strict_types=1);

use App\Containers\AppSection\CustomField\UI\Actions\DeleteCustomFieldAction;
use Illuminate\Support\Facades\Route;

Route::delete('app/custom-fields/{customField}', DeleteCustomFieldAction::class)
    ->middleware(['auth:api']);

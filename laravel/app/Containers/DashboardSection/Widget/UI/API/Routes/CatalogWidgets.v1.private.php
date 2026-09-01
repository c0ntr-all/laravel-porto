<?php declare(strict_types=1);

use App\Containers\DashboardSection\Widget\UI\Actions\ListWidgetCatalogAction;
use Illuminate\Support\Facades\Route;

Route::get('dashboard/widget-types', ListWidgetCatalogAction::class)
     ->middleware(['auth:api']);

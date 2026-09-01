<?php declare(strict_types=1);

use App\Containers\DashboardSection\Widget\UI\Actions\ListWidgetsAction;
use Illuminate\Support\Facades\Route;

Route::get('dashboards/{dashboard}/widgets', ListWidgetsAction::class)
     ->middleware(['auth:api']);

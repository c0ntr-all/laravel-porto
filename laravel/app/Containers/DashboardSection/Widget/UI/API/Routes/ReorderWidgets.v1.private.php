<?php declare(strict_types=1);

use App\Containers\DashboardSection\Widget\UI\Actions\ReorderWidgetsAction;
use Illuminate\Support\Facades\Route;

Route::post('dashboards/{dashboard}/widgets/reorder', ReorderWidgetsAction::class)
     ->middleware(['auth:api']);

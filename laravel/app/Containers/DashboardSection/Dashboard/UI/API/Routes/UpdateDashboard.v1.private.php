<?php declare(strict_types=1);

use App\Containers\DashboardSection\Dashboard\UI\Actions\UpdateDashboardAction;
use Illuminate\Support\Facades\Route;

Route::patch('dashboards/{dashboard}', UpdateDashboardAction::class)
     ->middleware(['auth:api']);

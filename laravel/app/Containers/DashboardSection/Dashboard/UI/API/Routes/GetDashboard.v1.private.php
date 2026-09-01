<?php declare(strict_types=1);

use App\Containers\DashboardSection\Dashboard\UI\Actions\GetDashboardAction;
use Illuminate\Support\Facades\Route;

Route::get('dashboards/{dashboard}', GetDashboardAction::class)
     ->middleware(['auth:api']);

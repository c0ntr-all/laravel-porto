<?php declare(strict_types=1);

use App\Containers\DashboardSection\Dashboard\UI\Actions\DeleteDashboardAction;
use Illuminate\Support\Facades\Route;

Route::delete('dashboards/{dashboard}', DeleteDashboardAction::class)
     ->middleware(['auth:api']);

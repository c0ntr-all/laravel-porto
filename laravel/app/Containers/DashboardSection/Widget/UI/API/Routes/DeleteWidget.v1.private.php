<?php declare(strict_types=1);

use App\Containers\DashboardSection\Widget\UI\Actions\DeleteWidgetAction;
use Illuminate\Support\Facades\Route;

Route::delete('dashboards/{dashboard}/widgets/{widget}', DeleteWidgetAction::class)
     ->middleware(['auth:api']);

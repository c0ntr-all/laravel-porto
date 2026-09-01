<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Providers;

use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Containers\DashboardSection\Widget\Tasks\DiscoverWidgetsTask;
use Illuminate\Support\ServiceProvider;

class WidgetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WidgetRegistry::class);
    }

    public function boot(DiscoverWidgetsTask $discoverWidgetsTask): void
    {
        $discoverWidgetsTask->run();
    }
}

<?php declare(strict_types=1);

namespace App\Ship\Providers;

use App\Ship\Traits\PortoPaths;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    use PortoPaths;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFromShip();
        $this->loadMigrationsFromContainers();
    }

    private function loadMigrations($directory): void
    {
        if (File::isDirectory($directory)) {
            $this->loadMigrationsFrom($directory);
        }
    }

    public function loadMigrationsFromContainers(): void
    {
        foreach($this->getAllContainerPaths() as $containerPath) {
            $containerMigrationDirectory = $containerPath . '/Data/Migrations';
            $this->loadMigrations($containerMigrationDirectory);
        }
    }

    public function loadMigrationsFromShip(): void
    {
        $shipMigrationDirectory = base_path('app/Ship/Migrations');
        $this->loadMigrations($shipMigrationDirectory);
    }
}

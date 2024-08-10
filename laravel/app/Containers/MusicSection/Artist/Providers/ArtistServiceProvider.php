<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Providers;

use Illuminate\Support\ServiceProvider;

class ArtistServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        $this->registerRoutes();
    }

    /**
     * @return void
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(
            path: __DIR__ . '/../Routes/Api/v1.php',
        );
    }
}

<?php

namespace App\Ship\Providers;

use App\Containers\AppSection\Authentication\Providers\AuthServiceProvider;
use App\Containers\AppSection\Authentication\Providers\JwtServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class ShipServiceProvider extends ServiceProvider
{
    private array $serviceProviders = [
        RouteServiceProvider::class,
        MigrationServiceProvider::class,
        AuthServiceProvider::class,
        JwtServiceProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->serviceProviders ?? [] as $provider) {
            if (class_exists($provider)) {
                App::register($provider);
            }
        }

        JsonResource::withoutWrapping();
        Passport::ignoreRoutes();

        Relation::enforceMorphMap([
            'artist' => 'App\Containers\MusicSection\Artist\Models\Artist',
            'album' => 'App\Containers\MusicSection\Album\Models\Album',
            'track' => 'App\Containers\MusicSection\Track\Models\Track',
            'user' => 'App\Containers\AppSection\User\Models\User',
            'task' => 'App\Containers\TaskManagerSection\Task\Models\Task',
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

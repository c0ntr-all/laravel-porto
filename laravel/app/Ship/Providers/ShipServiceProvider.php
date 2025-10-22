<?php declare(strict_types=1);

namespace App\Ship\Providers;

use App\Containers\AppSection\Authentication\Providers\AuthServiceProvider;
use App\Containers\AppSection\Authentication\Providers\JwtServiceProvider;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Loaders\ConfigsLoaderTrait;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class ShipServiceProvider extends ServiceProvider
{
    use ConfigsLoaderTrait;

    private array $serviceProviders = [
        RouteServiceProvider::class,
        EventServiceProvider::class,
        MigrationServiceProvider::class,
        AuthServiceProvider::class,
        JwtServiceProvider::class,
        TelescopeServiceProvider::class
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
            ContainerAliasEnum::USER->value => 'App\Containers\AppSection\User\Models\User',
            ContainerAliasEnum::MUSIC_ARTIST->value => 'App\Containers\MusicSection\Artist\Models\Artist',
            ContainerAliasEnum::MUSIC_ALBUM->value => 'App\Containers\MusicSection\Album\Models\Album',
            ContainerAliasEnum::MUSIC_TRACK->value => 'App\Containers\MusicSection\Track\Models\Track',
            ContainerAliasEnum::TM_TASK->value => 'App\Containers\TaskManagerSection\Task\Models\Task',
            ContainerAliasEnum::LL_POST->value => 'App\Containers\LifelogSection\Post\Models\Post',
            ContainerAliasEnum::GALLERY_IMAGE->value => 'App\Containers\GallerySection\Image\Models\Image'
        ]);

        $this->runConfigLoader();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

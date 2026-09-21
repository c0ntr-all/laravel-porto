<?php declare(strict_types=1);

namespace App\Ship\Providers;

use App\Containers\AppSection\Attachment\Providers\AttachmentServiceProvider;
use App\Containers\AppSection\Authentication\Providers\AuthServiceProvider;
use App\Containers\AppSection\Authentication\Providers\JwtServiceProvider;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Loaders\ConfigsLoaderTrait;
use Illuminate\Database\Eloquent\Factories\Factory;
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
        LocalizationServiceProvider::class,
        AuthServiceProvider::class,
        AttachmentServiceProvider::class,
        \App\Containers\AppSection\CustomField\Providers\CustomFieldServiceProvider::class,
        JwtServiceProvider::class,
        TelescopeServiceProvider::class,
        \App\Containers\AppSection\Notification\Providers\NotificationServiceProvider::class,
        \App\Containers\DashboardSection\Widget\Providers\WidgetServiceProvider::class,
        \App\Containers\MovieSection\Import\Providers\ImportServiceProvider::class,
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
            ContainerAliasEnum::LL_PRESET->value => 'App\Containers\LifelogSection\Preset\Models\Preset',
            ContainerAliasEnum::DASHBOARD->value => 'App\Containers\DashboardSection\Dashboard\Models\Dashboard',
            ContainerAliasEnum::DASHBOARD_WIDGET->value => 'App\Containers\DashboardSection\Widget\Models\Widget',
            ContainerAliasEnum::GALLERY_ALBUM->value => 'App\Containers\GallerySection\Album\Models\Album',
            ContainerAliasEnum::GALLERY_IMAGE->value => 'App\Containers\GallerySection\Image\Models\Image',
            ContainerAliasEnum::GALLERY_VIDEO->value => 'App\Containers\GallerySection\Video\Models\Video',
            ContainerAliasEnum::APP_DOCUMENT->value => 'App\Containers\AppSection\Document\Models\Document',
            ContainerAliasEnum::MOVIE->value => 'App\Containers\MovieSection\Movie\Models\Movie',
            ContainerAliasEnum::MOVIE_GENRE->value => 'App\Containers\MovieSection\Genre\Models\Genre',
            ContainerAliasEnum::COUNTRY->value => 'App\Containers\AppSection\Country\Models\Country',
            ContainerAliasEnum::MOVIE_IMPORT->value => 'App\Containers\MovieSection\Import\Models\MovieImport',
            ContainerAliasEnum::MOVIE_PERSON->value => 'App\Containers\MovieSection\Person\Models\Person',
            ContainerAliasEnum::MOVIE_PROFESSION->value => 'App\Containers\MovieSection\Profession\Models\Profession',
            // JSON:API resource names that may have been stored as morph types
            'albums' => 'App\Containers\GallerySection\Album\Models\Album',
            'images' => 'App\Containers\GallerySection\Image\Models\Image',
            'videos' => 'App\Containers\GallerySection\Video\Models\Video',
        ]);

        $this->runConfigLoader();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $parts = explode('\\', $modelName);
            $class = array_pop($parts);
            $last = array_pop($parts);

            // Если структура не совпадает — падаем обратно на стандартную логику
            if ($last !== 'Models') {
                return 'Database\\Factories\\' . $class . 'Factory';
            }

            $base = implode('\\', $parts);

            return $base . '\\Data\\Factories\\' . $class . 'Factory';
        });
    }
}

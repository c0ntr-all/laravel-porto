<?php declare(strict_types=1);

namespace App\Ship\Providers;

use App\Containers\MusicSection\Album\Models\AlbumType;
use App\Containers\MusicSection\Album\Observers\AlbumTypeObserver;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Observers\MusicTagObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        MusicTag::observe(MusicTagObserver::class);
        AlbumType::observe(AlbumTypeObserver::class);

        // Initialize cache if not exists
        if (!Cache::has('album_types') && Schema::hasTable((new AlbumType())->getTable())) {
            Cache::put('album_types', AlbumType::all(), now()->addDay());
        }
    }
}

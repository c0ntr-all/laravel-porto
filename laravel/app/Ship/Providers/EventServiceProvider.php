<?php declare(strict_types=1);

namespace App\Ship\Providers;

use App\Containers\AppSection\ActivityLog\Listeners\ActivityLogEventsSubscriber;
use App\Containers\AppSection\Attachment\Models\Attachment;
use App\Containers\AppSection\Attachment\Observers\AttachmentObserver;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\Models\Taggable;
use App\Containers\AppSection\Tag\Observers\TaggableObserver;
use App\Containers\AppSection\Tag\Observers\TagObserver;
use App\Containers\GallerySection\Image\Models\Image as GalleryImage;
use App\Containers\GallerySection\Image\Observers\ImageObserver as GalleryImageObserver;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Observers\PostObserver;
use App\Containers\MusicSection\Album\Models\AlbumType;
use App\Containers\MusicSection\Album\Observers\AlbumTypeObserver;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Observers\MusicTagObserver;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Observers\TaskObserver;
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

    protected $observers = [
        MusicTag::class => [MusicTagObserver::class],
        AlbumType::class => [AlbumTypeObserver::class],
        Post::class => [PostObserver::class],
        Task::class => [TaskObserver::class],
        GalleryImage::class => [GalleryImageObserver::class],
        Attachment::class => [AttachmentObserver::class],
        Tag::class => [TagObserver::class],
        Taggable::class => [TaggableObserver::class]
    ];

    protected $subscribe = [
        ActivityLogEventsSubscriber::class
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        // Initialize cache if not exists
        if (Schema::hasTable('cache') && Schema::hasTable((new AlbumType())->getTable()) && !Cache::has('album_types')) {
            Cache::put('album_types', AlbumType::all(), now()->addDay());
        }
    }
}

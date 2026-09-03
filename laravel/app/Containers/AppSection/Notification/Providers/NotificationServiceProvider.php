<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Providers;

use App\Containers\AppSection\Notification\Channels\DatabaseChannel;
use App\Containers\AppSection\Notification\Channels\EmailChannel;
use App\Containers\AppSection\Notification\Enums\NotificationChannelEnum;
use App\Containers\AppSection\Notification\Managers\NotificationChannelManager;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationChannelManager::class, function ($app) {
            $manager = new NotificationChannelManager();

            $channelMap = config('notifications.channels', [
                NotificationChannelEnum::EMAIL->value => EmailChannel::class,
                NotificationChannelEnum::DATABASE->value => DatabaseChannel::class,
            ]);

            foreach ($channelMap as $channelClass) {
                /** @var \App\Containers\AppSection\Notification\Contracts\NotificationChannelInterface $channel */
                $channel = $app->make($channelClass);
                $manager->register($channel);
            }

            return $manager;
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../Views',
            'notification'
        );

        Broadcast::routes([
            'middleware' => ['auth:api'],
            'prefix' => 'api',
        ]);
    }
}

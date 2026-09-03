<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tests\Functional;

use App\Containers\AppSection\Notification\Data\DTO\OutgoingNotificationData;
use App\Containers\AppSection\Notification\Enums\NotificationChannelEnum;
use App\Containers\AppSection\Notification\Enums\NotificationTypeEnum;
use App\Containers\AppSection\Notification\Models\UserNotification;
use App\Containers\AppSection\Notification\Tasks\SendNotificationTask;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_mark_and_count_notifications(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        app(SendNotificationTask::class)->run(
            $user,
            new OutgoingNotificationData(
                subject: 'Hello',
                body: 'World',
                type: NotificationTypeEnum::SYSTEM->value,
                data: ['entity' => 'demo'],
            ),
            [NotificationChannelEnum::DATABASE->value],
        );

        $notification = UserNotification::query()->firstOrFail();

        $this->getJson('/api/v1/app/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);

        $this->getJson('/api/v1/app/notifications?unread_only=1')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.is_read', false);

        $this->patchJson("/api/v1/app/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.attributes.is_read', true);

        $this->getJson('/api/v1/app/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 0);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $task = app(SendNotificationTask::class);

        foreach (range(1, 2) as $index) {
            $task->run(
                $user,
                new OutgoingNotificationData(
                    subject: "Notification {$index}",
                    body: 'Body',
                    type: NotificationTypeEnum::SYSTEM->value,
                ),
                [NotificationChannelEnum::DATABASE->value],
            );
        }

        $this->patchJson('/api/v1/app/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('data.updated_count', 2);

        $this->assertSame(0, UserNotification::query()->whereNull('read_at')->count());
    }

    public function test_user_can_paginate_notifications_by_page(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $task = app(SendNotificationTask::class);

        foreach (range(1, 3) as $index) {
            $task->run(
                $user,
                new OutgoingNotificationData(
                    subject: "Notification {$index}",
                    body: 'Body',
                    type: NotificationTypeEnum::SYSTEM->value,
                ),
                [NotificationChannelEnum::DATABASE->value],
            );
        }

        $this->getJson('/api/v1/app/notifications?page=1&per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3);

        $this->getJson('/api/v1/app/notifications?page=2&per_page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }
}

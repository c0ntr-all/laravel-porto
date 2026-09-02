<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tests\Unit;

use App\Containers\AppSection\Notification\Models\UserNotification;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_marked_as_read(): void
    {
        Event::fake();

        $user = User::factory()->create();

        $notification = UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'Test',
            'body' => 'Body',
            'data' => ['foo' => 'bar'],
        ]);

        $this->assertFalse($notification->isRead());

        $notification->markAsRead();

        $this->assertTrue($notification->refresh()->isRead());
        $this->assertNotNull($notification->read_at);
    }
}

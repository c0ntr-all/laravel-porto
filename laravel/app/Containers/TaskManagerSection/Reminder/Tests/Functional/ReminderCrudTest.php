<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Task with reminder',
        ]);
    }

    public function test_user_can_create_reminder_with_custom_interval(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder", [
                'datetime' => '2026-09-01 10:00',
                'is_active' => true,
                'interval' => [
                    'value' => 2,
                    'unit' => 'day',
                ],
                'to_remind_before' => [
                    'value' => 30,
                    'unit' => 'minute',
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.interval.value', 2)
            ->assertJsonPath('data.attributes.interval.unit', 'day')
            ->assertJsonPath('data.attributes.to_remind_before.value', 30)
            ->assertJsonPath('data.attributes.to_remind_before.unit', 'minute');

        $this->assertDatabaseHas('tm_reminders', [
            'task_id' => $this->task->id,
            'interval_value' => 2,
            'interval_unit' => 'day',
            'to_remind_before_value' => 30,
            'to_remind_before_unit' => 'minute',
        ]);
    }

    public function test_user_can_list_reminders(): void
    {
        Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-01 10:00:00',
            'is_active' => true,
            'next_remind_at' => '2026-09-01 10:00:00',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/task-manager/reminders');

        $response->assertOk()
            ->assertJsonPath('meta.count', 1);
    }

    public function test_user_can_get_reminder(): void
    {
        Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-01 10:00:00',
            'is_active' => true,
            'next_remind_at' => '2026-09-01 10:00:00',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder");

        $response->assertOk()
            ->assertJsonPath('data.type', 'reminders');
    }

    public function test_user_can_update_reminder(): void
    {
        Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-01 10:00:00',
            'interval_value' => 1,
            'interval_unit' => 'day',
            'is_active' => true,
            'next_remind_at' => '2026-09-01 10:00:00',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder", [
                'interval' => [
                    'value' => 3,
                    'unit' => 'week',
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.interval.value', 3)
            ->assertJsonPath('data.attributes.interval.unit', 'week');
    }

    public function test_user_can_delete_reminder(): void
    {
        Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-01 10:00:00',
            'is_active' => true,
            'next_remind_at' => '2026-09-01 10:00:00',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder");

        $response->assertOk();
        $this->assertDatabaseMissing('tm_reminders', ['task_id' => $this->task->id]);
    }

    public function test_cannot_create_second_reminder_for_same_task(): void
    {
        Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-01 10:00:00',
            'is_active' => true,
            'next_remind_at' => '2026-09-01 10:00:00',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder", [
                'datetime' => '2026-09-02 10:00',
            ]);

        $response->assertUnprocessable();
    }
}

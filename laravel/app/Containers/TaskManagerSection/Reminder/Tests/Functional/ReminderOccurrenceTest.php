<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Reminder\Enums\ReminderOccurrenceStatusEnum;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Models\ReminderOccurrence;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReminderOccurrenceTest extends TestCase
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
            'title' => 'Pay utilities',
        ]);
    }

    public function test_user_can_create_completed_occurrence_for_recurring_reminder(): void
    {
        Carbon::setTestNow('2026-09-12 15:00:00');

        Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-10 10:00:00',
            'interval_value' => 1,
            'interval_unit' => 'month',
            'is_active' => true,
            'next_remind_at' => null,
            'last_reminded_at' => '2026-09-09 10:00:00',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder/occurrences", [
                'status' => 'completed',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.status', 'completed')
            ->assertJsonPath('data.attributes.is_overdue', true)
            ->assertJsonPath('data.attributes.scheduled_at', '2026-09-10 10:00:00');

        $this->assertDatabaseHas('tm_reminders', [
            'task_id' => $this->task->id,
            'datetime' => '2026-10-10 10:00:00',
        ]);

        $this->assertDatabaseHas('tm_reminder_occurrences', [
            'task_id' => $this->task->id,
            'status' => ReminderOccurrenceStatusEnum::COMPLETED->value,
            'is_overdue' => 1,
        ]);

        Carbon::setTestNow();
    }

    public function test_user_can_list_reminder_occurrences(): void
    {
        $reminder = Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-10 10:00:00',
            'interval_value' => 1,
            'interval_unit' => 'month',
            'is_active' => true,
            'next_remind_at' => '2026-09-10 10:00:00',
        ]);

        ReminderOccurrence::create([
            'user_id' => $this->user->id,
            'reminder_id' => $reminder->id,
            'task_id' => $this->task->id,
            'status' => ReminderOccurrenceStatusEnum::COMPLETED,
            'scheduled_at' => '2026-08-10 10:00:00',
            'completed_at' => '2026-08-10 11:00:00',
            'is_overdue' => true,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder/occurrences");

        $response->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.is_overdue', true);
    }

    public function test_cannot_create_occurrence_for_inactive_reminder(): void
    {
        Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-10 10:00:00',
            'is_active' => false,
            'next_remind_at' => null,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder/occurrences", [
                'status' => 'completed',
            ]);

        $response->assertUnprocessable();
    }

    public function test_cannot_create_notified_occurrence_via_api(): void
    {
        Reminder::create([
            'user_id' => $this->user->id,
            'task_id' => $this->task->id,
            'datetime' => '2026-09-10 10:00:00',
            'is_active' => true,
            'next_remind_at' => '2026-09-10 10:00:00',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/task-manager/tasks/{$this->task->id}/reminder/occurrences", [
                'status' => 'notified',
            ]);

        $response->assertUnprocessable();
    }
}

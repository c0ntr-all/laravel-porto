<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Reminder\Enums\ReminderOccurrenceStatusEnum;
use App\Containers\TaskManagerSection\Reminder\Enums\ReminderTimeUnitEnum;
use App\Containers\TaskManagerSection\Reminder\Jobs\SendReminderNotificationJob;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Models\ReminderOccurrence;
use App\Containers\TaskManagerSection\Reminder\Services\ReminderScheduleCalculator;
use App\Containers\TaskManagerSection\Reminder\Tasks\AdvanceReminderScheduleTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\CompleteReminderTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\MarkReminderAsNotifiedTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\ProcessDueRemindersTask;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReminderScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculator_subtracts_remind_before_offset(): void
    {
        $calculator = new ReminderScheduleCalculator();
        $eventAt = Carbon::parse('2026-09-01 10:00:00');

        $next = $calculator->calculateNextRemindAt($eventAt, 30, ReminderTimeUnitEnum::MINUTE);

        $this->assertSame('2026-09-01 09:30:00', $next->format('Y-m-d H:i:s'));
    }

    public function test_calculator_advances_event_by_custom_interval(): void
    {
        $calculator = new ReminderScheduleCalculator();
        $eventAt = Carbon::parse('2026-09-01 10:00:00');

        $next = $calculator->advanceEventAt($eventAt, 3, ReminderTimeUnitEnum::WEEK);

        $this->assertSame('2026-09-22 10:00:00', $next->format('Y-m-d H:i:s'));
    }

    public function test_process_due_reminders_dispatches_jobs(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Due task',
        ]);

        Reminder::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'datetime' => now()->subHour(),
            'is_active' => true,
            'next_remind_at' => now()->subMinute(),
        ]);

        $count = app(ProcessDueRemindersTask::class)->run();

        $this->assertSame(1, $count);
        Queue::assertPushed(SendReminderNotificationJob::class);
    }

    public function test_recurring_reminder_awaits_completion_after_notify(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Recurring',
        ]);

        $reminder = Reminder::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'datetime' => '2026-09-01 10:00:00',
            'interval_value' => 2,
            'interval_unit' => 'day',
            'to_remind_before_value' => 1,
            'to_remind_before_unit' => 'hour',
            'is_active' => true,
            'next_remind_at' => '2026-09-01 09:00:00',
        ]);

        $result = app(MarkReminderAsNotifiedTask::class)->run($reminder);
        $updated = $result['reminder'];

        $this->assertTrue($updated->is_active);
        $this->assertTrue($updated->isAwaitingCompletion());
        $this->assertSame('2026-09-01 10:00:00', $updated->datetime->format('Y-m-d H:i:s'));
        $this->assertNull($updated->next_remind_at);
        $this->assertSame(ReminderOccurrenceStatusEnum::NOTIFIED, $result['occurrence']->status);
        $this->assertDatabaseCount('tm_reminder_occurrences', 1);
    }

    public function test_one_shot_reminder_is_deactivated_after_notify(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'One shot',
        ]);

        $reminder = Reminder::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'datetime' => '2026-09-01 10:00:00',
            'is_active' => true,
            'next_remind_at' => '2026-09-01 10:00:00',
        ]);

        $updated = app(MarkReminderAsNotifiedTask::class)->run($reminder)['reminder'];

        $this->assertFalse($updated->is_active);
        $this->assertNull($updated->next_remind_at);
        $this->assertNotNull($updated->last_reminded_at);
    }

    public function test_complete_recurring_reminder_advances_schedule_and_logs_overdue(): void
    {
        Carbon::setTestNow('2026-09-15 12:00:00');

        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Utilities',
        ]);

        $reminder = Reminder::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'datetime' => '2026-09-10 10:00:00',
            'interval_value' => 1,
            'interval_unit' => 'month',
            'is_active' => true,
            'next_remind_at' => null,
            'last_reminded_at' => '2026-09-09 10:00:00',
        ]);

        ReminderOccurrence::create([
            'user_id' => $user->id,
            'reminder_id' => $reminder->id,
            'task_id' => $task->id,
            'status' => ReminderOccurrenceStatusEnum::NOTIFIED,
            'scheduled_at' => '2026-09-10 10:00:00',
            'notified_at' => '2026-09-09 10:00:00',
            'is_overdue' => false,
        ]);

        $this->actingAs($user);

        $result = app(CompleteReminderTask::class)->run($reminder);
        $updated = $result['reminder'];
        $occurrence = $result['occurrence'];

        $this->assertTrue($occurrence->is_overdue);
        $this->assertSame(ReminderOccurrenceStatusEnum::COMPLETED, $occurrence->status);
        $this->assertSame('2026-10-10 10:00:00', $updated->datetime->format('Y-m-d H:i:s'));
        $this->assertNotNull($updated->next_remind_at);
        $this->assertNotNull($updated->last_completed_at);
        $this->assertFalse($updated->isAwaitingCompletion());

        Carbon::setTestNow();
    }

    public function test_complete_early_is_not_overdue(): void
    {
        Carbon::setTestNow('2026-09-05 12:00:00');

        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Early pay',
        ]);

        $reminder = Reminder::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'datetime' => '2026-09-10 10:00:00',
            'interval_value' => 1,
            'interval_unit' => 'month',
            'is_active' => true,
            'next_remind_at' => '2026-09-10 10:00:00',
        ]);

        $this->actingAs($user);

        $occurrence = app(CompleteReminderTask::class)->run($reminder)['occurrence'];

        $this->assertFalse($occurrence->is_overdue);
        $this->assertSame(ReminderOccurrenceStatusEnum::COMPLETED, $occurrence->status);

        Carbon::setTestNow();
    }

    public function test_advance_schedule_task_moves_recurring_event(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Advance',
        ]);

        $reminder = Reminder::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'datetime' => '2026-09-01 10:00:00',
            'interval_value' => 2,
            'interval_unit' => 'day',
            'to_remind_before_value' => 1,
            'to_remind_before_unit' => 'hour',
            'is_active' => true,
            'next_remind_at' => null,
        ]);

        $updated = app(AdvanceReminderScheduleTask::class)->run($reminder);

        $this->assertSame('2026-09-03 10:00:00', $updated->datetime->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-03 09:00:00', $updated->next_remind_at->format('Y-m-d H:i:s'));
    }
}

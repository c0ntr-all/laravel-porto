<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Reminder\Enums\ReminderTimeUnitEnum;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Services\ReminderScheduleCalculator;
use App\Containers\TaskManagerSection\Reminder\Tasks\AdvanceReminderAfterNotifyTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\ProcessDueRemindersTask;
use App\Containers\TaskManagerSection\Reminder\Jobs\SendReminderNotificationJob;
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

    public function test_recurring_reminder_is_advanced_after_notify(): void
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

        $updated = app(AdvanceReminderAfterNotifyTask::class)->run($reminder);

        $this->assertTrue($updated->is_active);
        $this->assertSame('2026-09-03 10:00:00', $updated->datetime->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-03 09:00:00', $updated->next_remind_at->format('Y-m-d H:i:s'));
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

        $updated = app(AdvanceReminderAfterNotifyTask::class)->run($reminder);

        $this->assertFalse($updated->is_active);
        $this->assertNull($updated->next_remind_at);
        $this->assertNotNull($updated->last_reminded_at);
    }
}

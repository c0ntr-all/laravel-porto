<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tests\Unit;

use App\Containers\AppSection\Notification\Enums\NotificationTypeEnum;
use App\Containers\AppSection\Notification\Events\UserNotificationCreated;
use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderUpdateData;
use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Enums\ReminderOccurrenceStatusEnum;
use App\Containers\TaskManagerSection\Reminder\Jobs\SendReminderNotificationJob;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\FindDueRemindersTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\MarkReminderAsNotifiedTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\ProcessDueReminderTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\SendReminderNotificationTask;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_due_reminder_with_offset_notifies_owner_once(): void
    {
        Mail::fake();
        Event::fake([UserNotificationCreated::class]);
        Carbon::setTestNow('2026-09-01 12:00:00');

        $reminder = $this->makeReminder([
            'datetime' => now()->addMinutes(20),
            'to_remind_before_value' => 30,
            'to_remind_before_unit' => 'minute',
            'next_remind_at' => now()->subMinutes(10),
        ]);

        $updated = app(ProcessDueReminderTask::class)->run($reminder);

        $this->assertNull($updated->next_remind_at);
        $this->assertNotNull($updated->last_reminded_at);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $reminder->user_id,
            'type' => NotificationTypeEnum::REMINDER_DUE->value,
        ]);
        $this->assertDatabaseCount('tm_reminder_occurrences', 1);
        $this->assertDatabaseHas('tm_reminder_occurrences', [
            'reminder_id' => $reminder->id,
            'user_id' => $reminder->user_id,
            'status' => ReminderOccurrenceStatusEnum::NOTIFIED->value,
        ]);
    }

    public function test_future_reminder_is_not_due(): void
    {
        Carbon::setTestNow('2026-09-01 12:00:00');

        $user = User::factory()->create();
        $dueTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Due',
        ]);
        $futureTask = Task::create([
            'user_id' => $user->id,
            'title' => 'Later',
        ]);

        $due = Reminder::create([
            'user_id' => $user->id,
            'task_id' => $dueTask->id,
            'datetime' => now()->subMinute(),
            'is_active' => true,
            'next_remind_at' => now()->subMinute(),
        ]);

        $future = Reminder::create([
            'user_id' => $user->id,
            'task_id' => $futureTask->id,
            'datetime' => now()->addHour(),
            'is_active' => true,
            'next_remind_at' => now()->addMinutes(10),
        ]);

        $ids = app(FindDueRemindersTask::class)->run()->pluck('id');

        $this->assertTrue($ids->contains($due->id));
        $this->assertFalse($ids->contains($future->id));
    }

    public function test_second_run_marks_occurrence_without_a_second_notification_when_mark_fails(): void
    {
        Mail::fake();
        Event::fake([UserNotificationCreated::class]);
        Carbon::setTestNow('2026-09-01 12:00:00');

        $reminder = $this->makeReminder();
        $realMark = app(MarkReminderAsNotifiedTask::class);
        $throw = true;

        $mark = \Mockery::mock(MarkReminderAsNotifiedTask::class);
        $mark->shouldReceive('run')->andReturnUsing(
            function (Reminder $reminder, ?Carbon $notifiedAt = null) use (&$throw, $realMark) {
                if ($throw) {
                    throw new RuntimeException('mark failed');
                }

                return $realMark->run($reminder, $notifiedAt);
            }
        );
        $this->app->instance(MarkReminderAsNotifiedTask::class, $mark);

        try {
            app(ProcessDueReminderTask::class)->run($reminder);
        } catch (RuntimeException $exception) {
            $this->assertSame('mark failed', $exception->getMessage());
        }

        $reminder->refresh();
        $this->assertNull($reminder->next_remind_at);
        $this->assertNotNull($reminder->last_reminded_at);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseCount('tm_reminder_occurrences', 0);

        $throw = false;
        $this->app->forgetInstance(ProcessDueReminderTask::class);
        app(ProcessDueReminderTask::class)->run($reminder->fresh());

        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseCount('tm_reminder_occurrences', 1);
        $this->assertDatabaseHas('tm_reminder_occurrences', [
            'reminder_id' => $reminder->id,
            'status' => ReminderOccurrenceStatusEnum::NOTIFIED->value,
        ]);
    }

    public function test_send_failure_before_insert_restores_next_remind_at(): void
    {
        Carbon::setTestNow('2026-09-01 12:00:00');

        $reminder = $this->makeReminder([
            'next_remind_at' => now()->subMinutes(10),
        ]);

        $send = \Mockery::mock(SendReminderNotificationTask::class);
        $send->shouldReceive('run')->once()->andThrow(new RuntimeException('send failed'));
        $this->app->instance(SendReminderNotificationTask::class, $send);

        try {
            app(ProcessDueReminderTask::class)->run($reminder);
            $this->fail('Send failure should retry the job.');
        } catch (RuntimeException $exception) {
            $this->assertSame('send failed', $exception->getMessage());
        }

        $reminder->refresh();
        $this->assertSame('2026-09-01 12:00:00', $reminder->next_remind_at?->format('Y-m-d H:i:s'));
        $this->assertNull($reminder->last_reminded_at);
        $this->assertDatabaseCount('user_notifications', 0);
    }

    public function test_update_of_awaiting_reminder_keeps_next_remind_at_null(): void
    {
        Carbon::setTestNow('2026-09-01 12:00:00');

        $reminder = $this->makeReminder([
            'datetime' => '2026-09-01 10:00:00',
            'interval_value' => 1,
            'interval_unit' => 'day',
            'to_remind_before_value' => 30,
            'to_remind_before_unit' => 'minute',
            'is_active' => true,
            'next_remind_at' => null,
            'last_reminded_at' => '2026-09-01 09:30:00',
        ]);

        $this->assertTrue($reminder->fresh()->isAwaitingCompletion());

        $updated = app(ReminderRepository::class)->update($reminder, ReminderUpdateData::from([
            'datetime' => '2026-09-01 10:00',
            'to_remind_before_value' => 30,
            'to_remind_before_unit' => 'minute',
            'interval_value' => 2,
            'interval_unit' => 'week',
        ]));

        $this->assertNull($updated->next_remind_at);
        $this->assertSame(2, $updated->interval_value);
        $this->assertTrue($updated->isAwaitingCompletion());
    }

    public function test_email_failure_after_insert_does_not_fail_or_duplicate(): void
    {
        Event::fake([UserNotificationCreated::class]);
        Carbon::setTestNow('2026-09-01 12:00:00');

        $pending = \Mockery::mock();
        $pending->shouldReceive('send')->once()->andThrow(new RuntimeException('smtp down'));
        Mail::shouldReceive('to')->once()->andReturn($pending);

        $reminder = $this->makeReminder();

        $updated = app(ProcessDueReminderTask::class)->run($reminder);

        $this->assertNull($updated->next_remind_at);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseCount('tm_reminder_occurrences', 1);
    }

    public function test_broadcast_failure_after_insert_does_not_fail_or_duplicate(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-09-01 12:00:00');
        config(['broadcasting.default' => 'log']);

        Event::listen(UserNotificationCreated::class, function (): void {
            throw new RuntimeException('reverb down');
        });

        $reminder = $this->makeReminder();

        $updated = app(ProcessDueReminderTask::class)->run($reminder);

        $this->assertNull($updated->next_remind_at);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseCount('tm_reminder_occurrences', 1);
    }

    public function test_claimed_reminder_without_a_send_is_due_again(): void
    {
        Mail::fake();
        Event::fake([UserNotificationCreated::class]);
        Carbon::setTestNow('2026-09-01 12:00:00');

        $reminder = $this->makeReminder([
            'datetime' => now()->addMinutes(20),
            'next_remind_at' => null,
            'last_reminded_at' => null,
            'is_active' => true,
        ]);

        $rearmed = app(ProcessDueReminderTask::class)->run($reminder);

        $this->assertSame('2026-09-01 12:00:00', $rearmed->next_remind_at?->format('Y-m-d H:i:s'));
        $this->assertDatabaseCount('user_notifications', 0);

        $this->app->forgetInstance(ProcessDueReminderTask::class);
        $sent = app(ProcessDueReminderTask::class)->run($rearmed->fresh());

        $this->assertNull($sent->next_remind_at);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseCount('tm_reminder_occurrences', 1);
    }

    public function test_send_job_is_unique_per_reminder(): void
    {
        $job = new SendReminderNotificationJob(15);

        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame('15', $job->uniqueId());
        $this->assertSame(120, $job->uniqueFor());
        $this->assertSame(3, $job->tries);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function makeReminder(array $overrides = []): Reminder
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Due task',
        ]);

        return Reminder::create(array_merge([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'datetime' => now()->addMinutes(20),
            'to_remind_before_value' => 30,
            'to_remind_before_unit' => 'minute',
            'is_active' => true,
            'next_remind_at' => now()->subMinutes(10),
        ], $overrides));
    }
}

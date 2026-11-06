<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Tests\Functional;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentsDeleteDto;
use App\Containers\AppSection\Attachment\Models\Attachment;
use App\Containers\AppSection\Attachment\Tasks\DeleteAttachmentsTask;
use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttachmentActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_delete_write_attached_and_detached_system_logs(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'With attachment',
        ]);

        $attachment = Attachment::create([
            'user_id' => $user->id,
            'attachable_type' => $task->getMorphClass(),
            'attachable_id' => $task->getKey(),
            'fileable_type' => ContainerAliasEnum::APP_DOCUMENT->value,
            'fileable_id' => '1',
        ]);

        $this->assertSame(1, DB::table('activity_system_logs')->where([
            'event_type' => EventTypesEnum::ATTACHED->value,
            'main_type' => $task->getMorphClass(),
            'main_id' => (string) $task->getKey(),
            'related_type' => ContainerAliasEnum::APP_DOCUMENT->value,
            'related_id' => '1',
        ])->count());

        app(DeleteAttachmentsTask::class)->run($task, AttachmentsDeleteDto::from([
            'deleted_attachments_ids' => [$attachment->id],
        ]));

        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        $this->assertSame(1, DB::table('activity_system_logs')->where([
            'event_type' => EventTypesEnum::DETACHED->value,
            'main_type' => $task->getMorphClass(),
            'main_id' => (string) $task->getKey(),
            'related_type' => ContainerAliasEnum::APP_DOCUMENT->value,
            'related_id' => '1',
        ])->count());
    }
}

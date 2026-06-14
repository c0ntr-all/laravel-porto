<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\DeleteTaskTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTaskTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_a_task(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task to delete',
        ]);

        $deleteTask = app(DeleteTaskTask::class);
        $result = $deleteTask->run($task);

        $this->assertTrue($result);
        $this->assertSoftDeleted('tm_tasks', ['id' => $task->id]);
    }

    public function test_it_returns_false_for_already_deleted_task(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Already deleted',
        ]);
        $task->delete();

        $deleteTask = app(DeleteTaskTask::class);
        $result = $deleteTask->run($task);

        $this->assertTrue($result);
    }
}

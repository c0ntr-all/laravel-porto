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

    public function test_it_soft_deletes_subtasks_with_parent(): void
    {
        $user = User::factory()->create();
        $parent = Task::create([
            'user_id' => $user->id,
            'title' => 'Parent',
        ]);
        $subtask = Task::create([
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'title' => 'Child',
        ]);

        $deleteTask = app(DeleteTaskTask::class);
        $deleteTask->run($parent);

        $this->assertSoftDeleted('tm_tasks', ['id' => $parent->id]);
        $this->assertSoftDeleted('tm_tasks', ['id' => $subtask->id]);
    }
}

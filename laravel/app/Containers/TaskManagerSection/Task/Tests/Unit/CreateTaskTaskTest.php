<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\CreateTaskTask;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_task(): void
    {
        $user = User::factory()->create();
        $task = app(CreateTaskTask::class);

        $dto = TaskCreateData::from([
            'user_id' => $user->id,
            'title' => 'Test Task',
            'content' => 'Test content',
        ]);

        $result = $task->run($dto);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('Test Task', $result->title);
        $this->assertEquals('Test content', $result->content);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertDatabaseHas('tm_tasks', [
            'title' => 'Test Task',
            'user_id' => $user->id,
        ]);
    }

    public function test_it_creates_a_task_with_task_list(): void
    {
        $user = User::factory()->create();
        $taskList = TaskList::create([
            'user_id' => $user->id,
            'title' => 'My List',
        ]);

        $task = app(CreateTaskTask::class);

        $dto = TaskCreateData::from([
            'user_id' => $user->id,
            'task_list_id' => $taskList->id,
            'title' => 'Task in List',
        ]);

        $result = $task->run($dto);

        $this->assertEquals($taskList->id, $result->task_list_id);
        $this->assertDatabaseHas('tm_tasks', [
            'title' => 'Task in List',
            'task_list_id' => $taskList->id,
        ]);
    }

    public function test_it_creates_a_task_without_content(): void
    {
        $user = User::factory()->create();
        $task = app(CreateTaskTask::class);

        $dto = TaskCreateData::from([
            'user_id' => $user->id,
            'title' => 'No Content Task',
        ]);

        $result = $task->run($dto);

        $this->assertNull($result->content);
    }
}

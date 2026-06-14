<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_task(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks', [
                'title' => 'New Task',
                'content' => 'Some content',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'attributes' => ['title', 'content']],
                'meta' => ['message'],
            ]);

        $this->assertDatabaseHas('tm_tasks', [
            'title' => 'New Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_create_task_in_list(): void
    {
        $taskList = TaskList::create([
            'user_id' => $this->user->id,
            'title' => 'My List',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks', [
                'title' => 'Task in List',
                'task_list_id' => $taskList->id,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('tm_tasks', [
            'title' => 'Task in List',
            'task_list_id' => $taskList->id,
        ]);
    }

    public function test_user_cannot_create_task_without_title(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks', [
                'content' => 'No title',
            ]);

        $response->assertUnprocessable();
    }

    public function test_unauthenticated_user_cannot_create_task(): void
    {
        $response = $this->postJson('/api/v1/task-manager/tasks', [
            'title' => 'Unauthorized Task',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_can_get_task(): void
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Get Me',
            'content' => 'Details here',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/task-manager/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'attributes' => ['title', 'content']],
            ]);
    }

    public function test_user_can_update_task(): void
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/task-manager/tasks/{$task->id}", [
                'title' => 'New Title',
                'content' => 'Updated content',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('tm_tasks', [
            'id' => $task->id,
            'title' => 'New Title',
        ]);
    }

    public function test_user_can_delete_task(): void
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Delete Me',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/task-manager/tasks/{$task->id}");

        $response->assertOk();

        $this->assertSoftDeleted('tm_tasks', ['id' => $task->id]);
    }
}

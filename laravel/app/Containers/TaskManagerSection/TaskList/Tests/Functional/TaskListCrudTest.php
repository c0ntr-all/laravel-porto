<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskListCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_task_list(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/task-lists', [
                'title' => 'Inbox Board',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.title', 'Inbox Board');

        $this->assertDatabaseHas('tm_task_lists', [
            'title' => 'Inbox Board',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_list_task_lists(): void
    {
        TaskList::create([
            'user_id' => $this->user->id,
            'title' => 'List A',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/task-manager/task-lists');

        $response->assertOk()
            ->assertJsonPath('meta.count', 1);
    }

    public function test_user_can_get_task_list_with_tasks(): void
    {
        $taskList = TaskList::create([
            'user_id' => $this->user->id,
            'title' => 'With Tasks',
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'task_list_id' => $taskList->id,
            'title' => 'Child Task',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/task-manager/task-lists/{$taskList->id}?include=tasks");

        $response->assertOk()
            ->assertJsonPath('data.attributes.title', 'With Tasks');
    }

    public function test_user_can_update_task_list(): void
    {
        $taskList = TaskList::create([
            'user_id' => $this->user->id,
            'title' => 'Old',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/task-manager/task-lists/{$taskList->id}", [
                'title' => 'New',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.title', 'New');
    }

    public function test_user_can_delete_task_list(): void
    {
        $taskList = TaskList::create([
            'user_id' => $this->user->id,
            'title' => 'Delete Me',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/task-manager/task-lists/{$taskList->id}");

        $response->assertOk();
        $this->assertSoftDeleted('tm_task_lists', ['id' => $taskList->id]);
    }
}

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

        $response->assertCreated()
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

        $response->assertCreated();

        $this->assertDatabaseHas('tm_tasks', [
            'title' => 'Task in List',
            'task_list_id' => $taskList->id,
        ]);
    }

    public function test_user_can_list_tasks(): void
    {
        Task::create([
            'user_id' => $this->user->id,
            'title' => 'Listed Task',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/task-manager/tasks');

        $response->assertOk()
            ->assertJsonPath('meta.count', 1);
    }

    public function test_user_can_list_unlisted_tasks(): void
    {
        $taskList = TaskList::create([
            'user_id' => $this->user->id,
            'title' => 'Board',
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'task_list_id' => $taskList->id,
            'title' => 'In List',
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'title' => 'Inbox',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/task-manager/tasks?unlisted=1');

        $response->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.title', 'Inbox');
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

    public function test_user_can_create_subtask(): void
    {
        $parent = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Parent Task',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks', [
                'title' => 'Subtask',
                'parent_id' => $parent->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.parent_id', (string) $parent->id);

        $this->assertDatabaseHas('tm_tasks', [
            'title' => 'Subtask',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_list_excludes_subtasks_by_default(): void
    {
        $parent = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Parent',
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'parent_id' => $parent->id,
            'title' => 'Child',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/task-manager/tasks');

        $response->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.title', 'Parent');
    }

    public function test_user_can_list_subtasks_by_parent_id(): void
    {
        $parent = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Parent',
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'parent_id' => $parent->id,
            'title' => 'Child',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/task-manager/tasks?parent_id={$parent->id}");

        $response->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.title', 'Child');
    }

    public function test_user_can_include_subtasks_and_parent(): void
    {
        $parent = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Parent',
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'parent_id' => $parent->id,
            'title' => 'Child',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/task-manager/tasks/{$parent->id}?include=subtasks");

        $response->assertOk()
            ->assertJsonPath('data.attributes.subtasks_count', 1);

        $childResponse = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/task-manager/tasks/" . $parent->subtasks()->first()->id . "?include=parent");

        $childResponse->assertOk()
            ->assertJsonPath('data.attributes.parent_id', (string) $parent->id);
    }

    public function test_user_can_mark_task_as_favorite(): void
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Favorite me',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/task-manager/tasks/{$task->id}", [
                'is_favorite' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.is_favorite', true);

        $this->assertDatabaseHas('tm_tasks', [
            'id' => $task->id,
            'is_favorite' => true,
        ]);
    }

    public function test_user_can_filter_favorite_tasks(): void
    {
        Task::create([
            'user_id' => $this->user->id,
            'title' => 'Regular',
            'is_favorite' => false,
            'priority' => 0,
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'title' => 'Starred',
            'is_favorite' => true,
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/task-manager/tasks?is_favorite=1');

        $response->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.title', 'Starred');
    }

    public function test_new_task_gets_auto_priority(): void
    {
        $first = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks', ['title' => 'First']);

        $second = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks', ['title' => 'Second']);

        $first->assertCreated()->assertJsonPath('data.attributes.priority', 0);
        $second->assertCreated()->assertJsonPath('data.attributes.priority', 1);
    }

    public function test_list_is_ordered_by_priority(): void
    {
        Task::create([
            'user_id' => $this->user->id,
            'title' => 'Low',
            'priority' => 10,
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'title' => 'High',
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/task-manager/tasks');

        $response->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'High')
            ->assertJsonPath('data.1.attributes.title', 'Low');
    }

    public function test_user_can_reorder_tasks(): void
    {
        $first = Task::create([
            'user_id' => $this->user->id,
            'title' => 'A',
            'priority' => 0,
        ]);

        $second = Task::create([
            'user_id' => $this->user->id,
            'title' => 'B',
            'priority' => 1,
        ]);

        $third = Task::create([
            'user_id' => $this->user->id,
            'title' => 'C',
            'priority' => 2,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks/reorder', [
                'ids' => [$third->id, $first->id, $second->id],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'C')
            ->assertJsonPath('data.0.attributes.priority', 0)
            ->assertJsonPath('data.1.attributes.title', 'A')
            ->assertJsonPath('data.1.attributes.priority', 1)
            ->assertJsonPath('data.2.attributes.title', 'B')
            ->assertJsonPath('data.2.attributes.priority', 2);
    }

    public function test_reorder_rejects_mixed_scopes(): void
    {
        $list = TaskList::create([
            'user_id' => $this->user->id,
            'title' => 'Board',
        ]);

        $inbox = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Inbox task',
            'priority' => 0,
        ]);

        $listed = Task::create([
            'user_id' => $this->user->id,
            'task_list_id' => $list->id,
            'title' => 'Listed task',
            'priority' => 0,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks/reorder', [
                'ids' => [$inbox->id, $listed->id],
            ]);

        $response->assertUnprocessable();
    }

    public function test_user_can_reorder_favorites_across_lists(): void
    {
        $list = TaskList::create([
            'user_id' => $this->user->id,
            'title' => 'Board',
        ]);

        $inbox = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Inbox favorite',
            'is_favorite' => true,
            'priority' => 0,
        ]);

        $listed = Task::create([
            'user_id' => $this->user->id,
            'task_list_id' => $list->id,
            'title' => 'Listed favorite',
            'is_favorite' => true,
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks/reorder', [
                'ids' => [$listed->id, $inbox->id],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Listed favorite')
            ->assertJsonPath('data.0.attributes.priority', 0)
            ->assertJsonPath('data.1.attributes.title', 'Inbox favorite')
            ->assertJsonPath('data.1.attributes.priority', 1);
    }
}

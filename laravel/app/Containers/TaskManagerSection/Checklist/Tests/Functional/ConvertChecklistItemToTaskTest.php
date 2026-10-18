<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConvertChecklistItemToTaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_convert_checklist_item_to_subtask(): void
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Parent Task',
        ]);

        $checklist = Checklist::create([
            'user_id' => $this->user->id,
            'task_id' => $task->id,
            'title' => 'Checklist',
        ]);

        $item = ChecklistItem::create([
            'user_id' => $this->user->id,
            'checklist_id' => $checklist->id,
            'title' => 'Item to promote',
            'position' => 0,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/task-manager/tasks/{$task->id}/checklists/{$checklist->id}/items/{$item->id}/convert-to-task");

        $response->assertCreated()
            ->assertJsonPath('data.attributes.title', 'Item to promote')
            ->assertJsonPath('data.attributes.parent_id', (string) $task->id);

        $createdTaskId = (int) $response->json('data.id');

        $this->assertDatabaseHas('tm_tasks', [
            'id' => $createdTaskId,
            'title' => 'Item to promote',
            'parent_id' => $task->id,
        ]);

        $this->assertDatabaseHas('tm_checklist_items', [
            'id' => $item->id,
            'converted_task_id' => $createdTaskId,
        ]);
    }

    public function test_cannot_convert_checklist_item_twice(): void
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Parent Task',
        ]);

        $checklist = Checklist::create([
            'user_id' => $this->user->id,
            'task_id' => $task->id,
            'title' => 'Checklist',
        ]);

        $convertedTask = Task::create([
            'user_id' => $this->user->id,
            'parent_id' => $task->id,
            'title' => 'Already converted',
        ]);

        $item = ChecklistItem::create([
            'user_id' => $this->user->id,
            'checklist_id' => $checklist->id,
            'title' => 'Item',
            'position' => 0,
            'converted_task_id' => $convertedTask->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/task-manager/tasks/{$task->id}/checklists/{$checklist->id}/items/{$item->id}/convert-to-task");

        $response->assertUnprocessable();
    }

    public function test_convert_rejects_item_from_another_checklist(): void
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Task A',
        ]);

        $checklist = Checklist::create([
            'user_id' => $this->user->id,
            'task_id' => $task->id,
            'title' => 'Checklist A',
        ]);

        $otherChecklist = Checklist::create([
            'user_id' => $this->user->id,
            'task_id' => $task->id,
            'title' => 'Checklist B',
        ]);

        $item = ChecklistItem::create([
            'user_id' => $this->user->id,
            'checklist_id' => $otherChecklist->id,
            'title' => 'Foreign item',
            'position' => 0,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/task-manager/tasks/{$task->id}/checklists/{$checklist->id}/items/{$item->id}/convert-to-task");

        $response->assertNotFound();
    }
}

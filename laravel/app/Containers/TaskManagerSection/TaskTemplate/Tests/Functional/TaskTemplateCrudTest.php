<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplateChecklist;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplateChecklistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTemplateCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_task_template_with_checklists(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/task-templates', [
                'title' => 'Deploy Template',
                'content' => 'Deploy checklist description',
                'checklists' => [
                    [
                        'title' => 'Pre-deploy',
                        'items' => [
                            ['title' => 'Run tests'],
                            ['title' => 'Backup DB'],
                        ],
                    ],
                    [
                        'title' => 'Post-deploy',
                        'items' => [
                            ['title' => 'Smoke check'],
                        ],
                    ],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.title', 'Deploy Template')
            ->assertJsonPath('meta.message', 'New task template successfully created!');

        $this->assertDatabaseHas('tm_task_templates', [
            'title' => 'Deploy Template',
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseCount('tm_task_template_checklists', 2);
        $this->assertDatabaseCount('tm_task_template_checklist_items', 3);
    }

    public function test_user_can_list_task_templates(): void
    {
        TaskTemplate::create([
            'user_id' => $this->user->id,
            'title' => 'Template A',
            'content' => 'A',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/task-manager/task-templates');

        $response->assertOk()
            ->assertJsonPath('meta.count', 1);
    }

    public function test_user_can_get_task_template(): void
    {
        $template = TaskTemplate::create([
            'user_id' => $this->user->id,
            'title' => 'Get Me',
            'content' => 'Details',
        ]);

        $checklist = TaskTemplateChecklist::create([
            'user_id' => $this->user->id,
            'task_template_id' => $template->id,
            'title' => 'Checklist',
            'position' => 0,
        ]);

        TaskTemplateChecklistItem::create([
            'user_id' => $this->user->id,
            'task_template_checklist_id' => $checklist->id,
            'title' => 'Item',
            'position' => 0,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/task-manager/task-templates/{$template->id}");

        $response->assertOk()
            ->assertJsonPath('data.attributes.title', 'Get Me');
    }

    public function test_user_can_update_task_template(): void
    {
        $template = TaskTemplate::create([
            'user_id' => $this->user->id,
            'title' => 'Old Title',
            'content' => 'Old content',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/task-manager/task-templates/{$template->id}", [
                'title' => 'New Title',
                'content' => 'New content',
                'checklists' => [
                    [
                        'title' => 'Updated checklist',
                        'items' => [
                            ['title' => 'Updated item'],
                        ],
                    ],
                ],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('tm_task_templates', [
            'id' => $template->id,
            'title' => 'New Title',
            'content' => 'New content',
        ]);

        $this->assertDatabaseHas('tm_task_template_checklists', [
            'task_template_id' => $template->id,
            'title' => 'Updated checklist',
        ]);
    }

    public function test_user_can_delete_task_template(): void
    {
        $template = TaskTemplate::create([
            'user_id' => $this->user->id,
            'title' => 'Delete Me',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/task-manager/task-templates/{$template->id}");

        $response->assertOk();

        $this->assertSoftDeleted('tm_task_templates', ['id' => $template->id]);
    }

    public function test_user_can_create_task_from_template(): void
    {
        $template = TaskTemplate::create([
            'user_id' => $this->user->id,
            'title' => 'Template Task Title',
            'content' => 'Template Task Content',
        ]);

        $checklist = TaskTemplateChecklist::create([
            'user_id' => $this->user->id,
            'task_template_id' => $template->id,
            'title' => 'Template Checklist',
            'position' => 0,
        ]);

        TaskTemplateChecklistItem::create([
            'user_id' => $this->user->id,
            'task_template_checklist_id' => $checklist->id,
            'title' => 'Template Item 1',
            'position' => 0,
        ]);

        TaskTemplateChecklistItem::create([
            'user_id' => $this->user->id,
            'task_template_checklist_id' => $checklist->id,
            'title' => 'Template Item 2',
            'position' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks', [
                'task_template_id' => $template->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.title', 'Template Task Title')
            ->assertJsonPath('data.attributes.content', 'Template Task Content');

        $this->assertDatabaseHas('tm_tasks', [
            'title' => 'Template Task Title',
            'content' => 'Template Task Content',
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('tm_checklists', [
            'title' => 'Template Checklist',
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('tm_checklist_items', [
            'title' => 'Template Item 1',
        ]);

        $this->assertDatabaseHas('tm_checklist_items', [
            'title' => 'Template Item 2',
        ]);
    }

    public function test_user_can_override_template_fields_when_creating_task(): void
    {
        $template = TaskTemplate::create([
            'user_id' => $this->user->id,
            'title' => 'Template Title',
            'content' => 'Template Content',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/task-manager/tasks', [
                'task_template_id' => $template->id,
                'title' => 'Overridden Title',
                'content' => 'Overridden Content',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.title', 'Overridden Title')
            ->assertJsonPath('data.attributes.content', 'Overridden Content');
    }

    public function test_unauthenticated_user_cannot_create_task_template(): void
    {
        $response = $this->postJson('/api/v1/task-manager/task-templates', [
            'title' => 'Unauthorized',
        ]);

        $response->assertUnauthorized();
    }
}

<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Dashboard $dashboard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
        $this->dashboard = Dashboard::create([
            'user_id' => $this->user->id,
            'name' => 'Main',
            'is_default' => true,
        ]);
    }

    public function test_catalog_contains_builtin_and_domain_widgets(): void
    {
        $response = $this->getJson('/api/v1/dashboard/widget-types');

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();

        $this->assertContains('dashboard.welcome', $types);
        $this->assertContains('dashboard.static-text', $types);
        $this->assertContains('dashboard.static-html', $types);
        $this->assertContains('lifelog.recent-posts', $types);
        $this->assertContains('task-manager.open-tasks', $types);
        $this->assertContains('music.recent-history', $types);
        $this->assertContains('gallery.recent-images', $types);
    }

    public function test_user_can_create_static_text_widget(): void
    {
        $response = $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets", [
            'type' => 'dashboard.static-text',
            'size' => 'third',
            'title' => 'Note',
            'config' => ['content' => 'Hello dashboard'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.type', 'dashboard.static-text')
            ->assertJsonPath('data.attributes.size', 'third')
            ->assertJsonPath('data.attributes.payload.data.content', 'Hello dashboard');
    }

    public function test_user_can_create_html_widget(): void
    {
        $response = $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets", [
            'type' => 'dashboard.static-html',
            'size' => 'full',
            'config' => ['html' => '<strong>Hi</strong>'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.size', 'full')
            ->assertJsonPath('data.attributes.payload.html', '<strong>Hi</strong>');
    }

    public function test_user_cannot_create_unknown_widget_type(): void
    {
        $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets", [
            'type' => 'unknown.missing',
        ])->assertUnprocessable();
    }

    public function test_recent_posts_widget_returns_items(): void
    {
        Post::withoutEvents(function () {
            Post::factory()->create([
                'user_id' => $this->user->id,
                'title' => 'Today',
            ]);
        });

        $response = $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets", [
            'type' => 'lifelog.recent-posts',
            'size' => 'half',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.payload.view', 'list')
            ->assertJsonPath('data.attributes.payload.data.items.0.title', 'Today');
    }

    public function test_user_can_update_widget_size_and_reorder(): void
    {
        $first = $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets", [
            'type' => 'dashboard.welcome',
            'size' => 'full',
        ])->json('data.id');

        $second = $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets", [
            'type' => 'dashboard.static-text',
            'size' => 'half',
            'config' => ['content' => 'x'],
        ])->json('data.id');

        $this->patchJson("/api/v1/dashboards/{$this->dashboard->id}/widgets/{$second}", [
            'size' => 'third',
            'title' => 'Renamed',
        ])->assertOk()
            ->assertJsonPath('data.attributes.size', 'third')
            ->assertJsonPath('data.attributes.title', 'Renamed');

        $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets/reorder", [
            'ids' => [(int) $second, (int) $first],
        ])->assertOk();

        $this->assertSame((int) $second, (int) Widget::query()->orderBy('sort_order')->first()->id);
    }

    public function test_user_can_delete_widget(): void
    {
        $id = $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets", [
            'type' => 'dashboard.welcome',
        ])->json('data.id');

        $this->deleteJson("/api/v1/dashboards/{$this->dashboard->id}/widgets/{$id}")
            ->assertOk();

        $this->assertSoftDeleted('dashboard_widgets', ['id' => $id]);
    }

    public function test_get_dashboard_includes_widget_payloads(): void
    {
        $this->postJson("/api/v1/dashboards/{$this->dashboard->id}/widgets", [
            'type' => 'dashboard.welcome',
            'size' => 'full',
        ]);

        $response = $this->getJson("/api/v1/dashboards/{$this->dashboard->id}");

        $response->assertOk()
            ->assertJsonPath('data.attributes.name', 'Main');

        $included = collect($response->json('included') ?? []);
        $widget = $included->firstWhere('type', 'dashboard_widgets');

        $this->assertNotNull($widget);
        $this->assertSame('welcome', $widget['attributes']['payload']['view']);
        $this->assertSame($this->user->name, $widget['attributes']['payload']['data']['name']);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_listing_creates_a_default_dashboard(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/dashboards');

        $response->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.name', 'Главная')
            ->assertJsonPath('data.0.attributes.is_default', true);

        $this->assertDatabaseHas('dashboards', [
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $this->assertGreaterThan(0, Dashboard::query()->first()->widgets()->count());
    }

    public function test_user_can_create_dashboard(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/dashboards', [
                'name' => 'Work',
                'description' => 'Tasks only',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.name', 'Work')
            ->assertJsonPath('data.attributes.widgets_count', 0);

        $this->assertDatabaseHas('dashboards', [
            'name' => 'Work',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_create_dashboard_with_default_widgets(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/dashboards', [
                'name' => 'Home',
                'with_defaults' => true,
            ]);

        $response->assertCreated();
        $this->assertGreaterThan(0, $response->json('data.attributes.widgets_count'));
    }

    public function test_user_can_update_and_set_default_dashboard(): void
    {
        $first = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/dashboards', ['name' => 'One', 'is_default' => true])
            ->json('data.id');

        $second = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/dashboards', ['name' => 'Two'])
            ->json('data.id');

        $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/dashboards/{$second}", ['name' => 'Two updated'])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Two updated');

        $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/dashboards/{$second}/default")
            ->assertOk()
            ->assertJsonPath('data.attributes.is_default', true);

        $this->assertDatabaseHas('dashboards', ['id' => $second, 'is_default' => true]);
        $this->assertDatabaseHas('dashboards', ['id' => $first, 'is_default' => false]);
    }

    public function test_user_can_delete_dashboard(): void
    {
        $id = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/dashboards', ['name' => 'Temp'])
            ->json('data.id');

        $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/dashboards/{$id}")
            ->assertOk();

        $this->assertSoftDeleted('dashboards', ['id' => $id]);
    }

    public function test_unauthenticated_user_cannot_list_dashboards(): void
    {
        $this->getJson('/api/v1/dashboards')->assertUnauthorized();
    }

    public function test_user_cannot_see_another_users_dashboard(): void
    {
        $other = User::factory()->create();
        $dashboard = Dashboard::create([
            'user_id' => $other->id,
            'name' => 'Secret',
            'is_default' => true,
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/dashboards/{$dashboard->id}")
            ->assertNotFound();
    }
}

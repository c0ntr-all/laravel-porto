<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Profession\Models\Profession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_professions(): void
    {
        $this->getJson('/api/v1/movie/professions')->assertUnauthorized();
    }

    public function test_user_can_create_list_get_update_and_delete_profession(): void
    {
        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/professions', [
                'en_name' => 'actor',
                'name' => 'актеры',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'movie_professions')
            ->assertJsonPath('data.attributes.en_name', 'actor')
            ->assertJsonPath('data.attributes.name', 'актеры');

        $id = (int) $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/professions')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/professions/'.$id)
            ->assertOk()
            ->assertJsonPath('data.attributes.en_name', 'actor');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/professions/'.$id, [
                'name' => 'актёр',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'актёр');

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/professions/'.$id)
            ->assertOk();

        $this->assertDatabaseMissing('movie_professions', ['id' => $id]);
    }

    public function test_user_cannot_create_duplicate_en_name(): void
    {
        Profession::factory()->create(['en_name' => 'director']);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/professions', [
                'en_name' => 'director',
            ])
            ->assertUnprocessable();
    }
}

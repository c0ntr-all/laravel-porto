<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Genre\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_genres(): void
    {
        $this->getJson('/api/v1/movie/genres')->assertUnauthorized();
    }

    public function test_user_can_create_genre(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/genres', [
                'name' => 'Drama',
                'kp_id' => 8,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'movie_genres')
            ->assertJsonPath('data.attributes.name', 'Drama')
            ->assertJsonPath('data.attributes.slug', 'drama')
            ->assertJsonPath('data.attributes.kp_id', 8);

        $this->assertDatabaseHas('movie_genres', [
            'name' => 'Drama',
            'slug' => 'drama',
            'kp_id' => 8,
        ]);
    }

    public function test_user_cannot_create_duplicate_genre_name(): void
    {
        Genre::factory()->create(['name' => 'Comedy', 'slug' => 'comedy']);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/genres', [
                'name' => 'Comedy',
            ])
            ->assertUnprocessable();
    }

    public function test_user_can_list_genres(): void
    {
        Genre::factory()->create(['name' => 'Action', 'slug' => 'action']);
        Genre::factory()->create(['name' => 'Thriller', 'slug' => 'thriller']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/genres');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_get_update_and_delete_genre(): void
    {
        $genre = Genre::factory()->create(['name' => 'Sci-Fi', 'slug' => 'sci-fi']);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/genres/'.$genre->id)
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Sci-Fi');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/genres/'.$genre->id, [
                'name' => 'Science Fiction',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Science Fiction')
            ->assertJsonPath('data.attributes.slug', 'science-fiction');

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/genres/'.$genre->id)
            ->assertOk();

        $this->assertDatabaseMissing('movie_genres', ['id' => $genre->id]);
    }
}

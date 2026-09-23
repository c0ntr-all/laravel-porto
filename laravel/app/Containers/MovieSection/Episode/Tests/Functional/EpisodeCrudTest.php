<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Season\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpisodeCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_episodes(): void
    {
        $this->getJson('/api/v1/movie/episodes')->assertUnauthorized();
    }

    public function test_user_can_create_list_get_update_and_delete_episode(): void
    {
        $series = Movie::factory()->create(['type' => MovieTypeEnum::TV_SERIES]);
        $season = Season::factory()->create([
            'movie_id' => $series->id,
            'number' => 1,
            'kp_season_id' => 555,
        ]);

        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/episodes', [
                'season_id' => $season->id,
                'kp_id' => 444,
                'kp_season_id' => 555,
                'name' => 'Пилот',
                'description' => 'Уолтер Уайт узнаёт страшный диагноз.',
                'en_description' => 'Walter White learns a terrible diagnosis.',
                'number' => 1,
                'duration' => 58,
                'air_date' => '2008-01-20',
                'still' => 'https://example.com/e1.jpg',
                'still_preview' => 'https://example.com/e1-preview.jpg',
            ]);

        $created->assertCreated()
            ->assertJsonPath('data.type', 'movie_episodes')
            ->assertJsonPath('data.attributes.name', 'Пилот')
            ->assertJsonPath('data.attributes.number', 1)
            ->assertJsonPath('data.attributes.kp_id', 444)
            ->assertJsonPath('data.attributes.kp_season_id', 555)
            ->assertJsonPath('data.attributes.air_date', '2008-01-20');

        $id = (int) $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/episodes?filter[season_id]='.$season->id)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/episodes/'.$id)
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Пилот');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/episodes/'.$id, [
                'name' => 'Пилотная серия',
                'duration' => 60,
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Пилотная серия')
            ->assertJsonPath('data.attributes.duration', 60);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/episodes/'.$id)
            ->assertOk();

        $this->assertDatabaseMissing('movie_episodes', ['id' => $id]);
    }

    public function test_user_cannot_create_duplicate_episode_number_in_season(): void
    {
        $season = Season::factory()->create(['number' => 1]);
        Episode::factory()->create([
            'season_id' => $season->id,
            'number' => 1,
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/episodes', [
                'season_id' => $season->id,
                'number' => 1,
            ])
            ->assertUnprocessable();
    }

    public function test_deleting_season_cascades_episodes(): void
    {
        $season = Season::factory()->create(['number' => 1]);
        $episode = Episode::factory()->create([
            'season_id' => $season->id,
            'number' => 1,
        ]);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/seasons/'.$season->id)
            ->assertOk();

        $this->assertDatabaseMissing('movie_episodes', ['id' => $episode->id]);
    }
}

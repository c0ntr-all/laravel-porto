<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Season\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_seasons(): void
    {
        $this->getJson('/api/v1/movie/seasons')->assertUnauthorized();
    }

    public function test_user_can_create_list_get_update_and_delete_season(): void
    {
        $series = Movie::factory()->create([
            'title' => 'Breaking Bad',
            'type' => MovieTypeEnum::TV_SERIES,
        ]);

        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/seasons', [
                'movie_id' => $series->id,
                'kp_id' => 111,
                'kp_season_id' => 222,
                'kp_movie_id' => 333,
                'name' => 'Сезон 1',
                'en_name' => 'Season 1',
                'number' => 1,
                'air_date' => '2008-01-20',
                'episodes_count' => 7,
                'duration' => 47,
                'poster' => 'https://example.com/s1.jpg',
                'poster_preview' => 'https://example.com/s1-preview.jpg',
            ]);

        $created->assertCreated()
            ->assertJsonPath('data.type', 'movie_seasons')
            ->assertJsonPath('data.attributes.name', 'Сезон 1')
            ->assertJsonPath('data.attributes.number', 1)
            ->assertJsonPath('data.attributes.kp_id', 111)
            ->assertJsonPath('data.attributes.kp_season_id', 222)
            ->assertJsonPath('data.attributes.kp_movie_id', 333)
            ->assertJsonPath('data.attributes.air_date', '2008-01-20');

        $id = (int) $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/seasons?filter[movie_id]='.$series->id)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/seasons/'.$id)
            ->assertOk()
            ->assertJsonPath('data.attributes.en_name', 'Season 1');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/seasons/'.$id, [
                'name' => 'Первый сезон',
                'episodes_count' => 8,
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Первый сезон')
            ->assertJsonPath('data.attributes.episodes_count', 8);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/seasons/'.$id)
            ->assertOk();

        $this->assertDatabaseMissing('movie_seasons', ['id' => $id]);
    }

    public function test_user_cannot_create_duplicate_season_number_for_same_movie(): void
    {
        $series = Movie::factory()->create(['type' => MovieTypeEnum::TV_SERIES]);
        Season::factory()->create([
            'movie_id' => $series->id,
            'number' => 1,
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/seasons', [
                'movie_id' => $series->id,
                'number' => 1,
            ])
            ->assertUnprocessable();
    }

    public function test_tv_series_movie_show_includes_seasons(): void
    {
        $series = Movie::factory()->create([
            'title' => 'Breaking Bad',
            'type' => MovieTypeEnum::TV_SERIES,
        ]);
        $season = Season::factory()->create([
            'movie_id' => $series->id,
            'number' => 1,
            'name' => 'Сезон 1',
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies/'.$series->id)
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'Breaking Bad');

        $included = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies/'.$series->id.'?include=seasons')
            ->assertOk();

        $types = collect($included->json('included'))->pluck('type')->unique()->values()->all();
        $this->assertContains('movie_seasons', $types);
        $this->assertSame($season->id, (int) collect($included->json('included'))
            ->firstWhere('type', 'movie_seasons')['id']);
    }
}

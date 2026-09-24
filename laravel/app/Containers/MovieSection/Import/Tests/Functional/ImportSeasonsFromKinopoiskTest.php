<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Season\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportSeasonsFromKinopoiskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        config()->set('movie_import.token', 'test-api-key');
        config()->set('movie_import.base_url', 'https://api.poiskkino.dev');
        config()->set('movie_import.season_path', '/v1.5/season');
        config()->set('movie_import.season_page_limit', 250);
        config()->set('movie_import.retries', 2);
        config()->set('movie_import.retry_sleep_ms', 0);
    }

    public function test_guest_cannot_import_seasons(): void
    {
        $this->postJson('/api/v1/movie/imports/seasons', ['kp_id' => 404900])
            ->assertUnauthorized();
    }

    public function test_requires_existing_local_movie(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports/seasons', ['kp_id' => 404900])
            ->assertUnprocessable();
    }

    public function test_imports_seasons_and_episodes_by_series_kp_id(): void
    {
        $movie = Movie::factory()->create([
            'kp_id' => 404900,
            'title' => 'Во все тяжкие',
            'type' => MovieTypeEnum::TV_SERIES,
        ]);

        Http::fake([
            'https://api.poiskkino.dev/v1.5/season*' => Http::response($this->seasonsPayload(), 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports/seasons', ['kp_id' => 404900]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'movies')
            ->assertJsonPath('data.id', (string) $movie->id)
            ->assertJsonPath('meta.seasons_total', 1)
            ->assertJsonPath('meta.seasons_created', 1)
            ->assertJsonPath('meta.episodes_total', 2)
            ->assertJsonPath('meta.episodes_created', 2);

        $this->assertDatabaseHas('movie_seasons', [
            'movie_id' => $movie->id,
            'number' => 1,
            'name' => 'Сезон 1',
            'en_name' => 'Season 1',
            'kp_movie_id' => 404900,
            'episodes_count' => 7,
            'air_date' => '2008-01-20',
        ]);
        $this->assertDatabaseHas('movie_episodes', [
            'name' => 'Пилот',
            'number' => 1,
            'air_date' => '2008-01-20',
            'still' => 'https://example.com/e1.jpg',
        ]);
        $this->assertDatabaseHas('movie_episodes', [
            'name' => 'Кот в мешке…',
            'number' => 2,
        ]);

        $includedTypes = collect($response->json('included'))->pluck('type')->unique()->values()->all();
        $this->assertContains('movie_seasons', $includedTypes);
        $this->assertContains('movie_episodes', $includedTypes);
    }

    public function test_reimport_updates_existing_seasons_and_episodes(): void
    {
        $movie = Movie::factory()->create([
            'kp_id' => 404900,
            'type' => MovieTypeEnum::TV_SERIES,
        ]);
        $season = Season::factory()->create([
            'movie_id' => $movie->id,
            'number' => 1,
            'name' => 'Old Season',
            'kp_movie_id' => 404900,
            'episodes_count' => 1,
        ]);
        Episode::factory()->create([
            'season_id' => $season->id,
            'number' => 1,
            'name' => 'Old Pilot',
        ]);

        Http::fake([
            'https://api.poiskkino.dev/v1.5/season*' => Http::response($this->seasonsPayload(), 200),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports/seasons', ['kp_id' => 404900])
            ->assertOk()
            ->assertJsonPath('meta.seasons_created', 0)
            ->assertJsonPath('meta.seasons_updated', 1)
            ->assertJsonPath('meta.episodes_created', 1)
            ->assertJsonPath('meta.episodes_updated', 1);

        $this->assertDatabaseHas('movie_seasons', [
            'id' => $season->id,
            'name' => 'Сезон 1',
            'episodes_count' => 7,
        ]);
        $this->assertDatabaseHas('movie_episodes', [
            'season_id' => $season->id,
            'number' => 1,
            'name' => 'Пилот',
        ]);
        $this->assertDatabaseCount('movie_seasons', 1);
        $this->assertDatabaseCount('movie_episodes', 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function seasonsPayload(): array
    {
        return [
            'docs' => [
                [
                    'movieId' => 404900,
                    'number' => 1,
                    'name' => 'Сезон 1',
                    'enName' => 'Season 1',
                    'airDate' => '2008-01-20T00:00:00.000Z',
                    'episodesCount' => 7,
                    'duration' => 47,
                    'poster' => [
                        'url' => 'https://example.com/s1.jpg',
                        'previewUrl' => 'https://example.com/s1-preview.jpg',
                    ],
                    'episodes' => [
                        [
                            'number' => 1,
                            'name' => 'Пилот',
                            'enName' => 'Pilot',
                            'description' => 'Уолтер Уайт узнаёт диагноз.',
                            'enDescription' => 'Walter White learns a diagnosis.',
                            'duration' => 58,
                            'airDate' => '2008-01-20',
                            'still' => [
                                'url' => 'https://example.com/e1.jpg',
                                'previewUrl' => 'https://example.com/e1-preview.jpg',
                            ],
                        ],
                        [
                            'number' => 2,
                            'name' => 'Кот в мешке…',
                            'airDate' => '2008-01-27',
                        ],
                    ],
                ],
            ],
            'total' => 1,
            'limit' => 250,
            'hasNext' => false,
            'hasPrev' => false,
            'next' => null,
            'prev' => null,
        ];
    }
}

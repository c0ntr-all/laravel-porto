<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_search_ranks_exact_title_before_similar_prefixes(): void
    {
        $exact = Movie::factory()->create(['title' => 'Тор']);
        Movie::factory()->create(['title' => 'Торнадо']);
        Movie::factory()->create(['title' => 'Торчвуд']);
        Movie::factory()->create(['title' => 'Тор: Рагнарёк']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies?' . http_build_query([
                'filter' => ['search' => 'Тор'],
            ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertSame((int) $exact->id, $ids[0]);
        $this->assertContains((int) Movie::query()->where('title', 'Тор: Рагнарёк')->value('id'), $ids);
        $this->assertTrue(
            array_search((int) Movie::query()->where('title', 'Тор: Рагнарёк')->value('id'), $ids, true)
            < array_search((int) Movie::query()->where('title', 'Торнадо')->value('id'), $ids, true)
        );
    }

    public function test_search_finds_movie_when_query_typed_in_wrong_layout(): void
    {
        $thor = Movie::factory()->create(['title' => 'Тор']);
        Movie::factory()->create(['title' => 'Матрица']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies?' . http_build_query([
                'filter' => ['search' => 'Njh'],
            ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertSame([(int) $thor->id], $ids);
    }

    public function test_search_still_matches_description(): void
    {
        $movie = Movie::factory()->create([
            'title' => 'Скрытый хит',
            'description' => 'История про Тор в современном мире',
        ]);
        Movie::factory()->create(['title' => 'Другое кино', 'description' => 'Без совпадений']);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies?' . http_build_query([
                'filter' => ['search' => 'Тор'],
            ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertContains((int) $movie->id, $ids);
    }
}

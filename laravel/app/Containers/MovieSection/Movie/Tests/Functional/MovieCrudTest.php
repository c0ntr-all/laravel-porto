<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tests\Functional;

use App\Containers\AppSection\Country\Models\Country;
use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Profession\Models\Profession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_movies(): void
    {
        $this->getJson('/api/v1/movie/movies')->assertUnauthorized();
    }

    public function test_user_can_create_movie_with_genres_and_countries(): void
    {
        $drama = Genre::factory()->create(['name' => 'Drama', 'slug' => 'drama']);
        $crime = Genre::factory()->create(['name' => 'Crime', 'slug' => 'crime']);
        $usa = Country::create(['name' => 'USA']);
        $uk = Country::create(['name' => 'United Kingdom']);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/movies', [
                'kp_id' => 326,
                'title' => 'Pulp Fiction',
                'year' => 1994,
                'type' => MovieTypeEnum::MOVIE->value,
                'cover' => 'https://example.com/cover.jpg',
                'kp_rating' => 8.6,
                'kp_img' => 'https://kinopoisk.ru/pulp.jpg',
                'description' => 'Криминальная история из Лос-Анджелеса.',
                'short_description' => 'Криминальная комедия Тарантино.',
                'genre_ids' => [$drama->id, $crime->id],
                'country_ids' => [$usa->id, $uk->id],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'movies')
            ->assertJsonPath('data.attributes.title', 'Pulp Fiction')
            ->assertJsonPath('data.attributes.kp_id', 326)
            ->assertJsonPath('data.attributes.year', 1994)
            ->assertJsonPath('data.attributes.type', 'movie')
            ->assertJsonPath('data.attributes.cover', 'https://example.com/cover.jpg')
            ->assertJsonPath('data.attributes.kp_rating', 8.6)
            ->assertJsonPath('data.attributes.kp_img', 'https://kinopoisk.ru/pulp.jpg')
            ->assertJsonPath('data.attributes.description', 'Криминальная история из Лос-Анджелеса.')
            ->assertJsonPath('data.attributes.short_description', 'Криминальная комедия Тарантино.');

        $includedTypes = collect($response->json('included'))->pluck('type')->unique()->values()->all();
        $this->assertContains('movie_genres', $includedTypes);
        $this->assertContains('countries', $includedTypes);

        $this->assertDatabaseHas('movies', [
            'kp_id' => 326,
            'title' => 'Pulp Fiction',
        ]);
        $this->assertDatabaseCount('movie_genre', 2);
        $this->assertDatabaseCount('movie_country', 2);
    }

    public function test_user_cannot_create_movie_with_duplicate_kp_id(): void
    {
        Movie::factory()->create(['kp_id' => 326]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/movies', [
                'kp_id' => 326,
                'title' => 'Another Film',
                'year' => 2000,
                'type' => MovieTypeEnum::MOVIE->value,
            ])
            ->assertUnprocessable();
    }

    public function test_user_can_list_filter_get_update_and_delete_movie(): void
    {
        $drama = Genre::factory()->create(['name' => 'Drama', 'slug' => 'drama']);
        $usa = Country::create(['name' => 'USA']);
        $movie = Movie::factory()->create([
            'title' => 'The Matrix',
            'year' => 1999,
            'type' => MovieTypeEnum::MOVIE,
        ]);
        $movie->genres()->attach($drama);
        $movie->countries()->attach($usa);
        Movie::factory()->create([
            'title' => 'Breaking Bad',
            'year' => 2008,
            'type' => MovieTypeEnum::TV_SERIES,
        ]);

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies?filter[type]=movie');

        $list->assertOk();
        $this->assertCount(1, $list->json('data'));
        $this->assertSame($movie->id, (int) $list->json('data.0.id'));

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies/'.$movie->id)
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'The Matrix');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/movies/'.$movie->id, [
                'title' => 'The Matrix Reloaded',
                'genre_ids' => [],
                'country_ids' => [$usa->id],
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'The Matrix Reloaded');

        $this->assertDatabaseCount('movie_genre', 0);
        $this->assertDatabaseCount('movie_country', 1);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/movies/'.$movie->id)
            ->assertOk();

        $this->assertDatabaseMissing('movies', ['id' => $movie->id]);
    }

    public function test_movie_returns_actors_count_preview_and_full_credits_list(): void
    {
        $actor = Profession::factory()->create(['en_name' => 'actor', 'name' => 'актеры']);
        $director = Profession::factory()->create(['en_name' => 'director', 'name' => 'режиссеры']);
        $movie = Movie::factory()->create();
        $directorPerson = Person::factory()->create(['profession_id' => $director->id]);
        $movie->persons()->attach($directorPerson->id, [
            'profession_id' => $director->id,
            'description' => null,
        ]);

        for ($index = 1; $index <= 12; $index++) {
            $person = Person::factory()->create(['profession_id' => $actor->id]);
            $movie->persons()->attach($person->id, [
                'profession_id' => $actor->id,
                'description' => 'Role '.$index,
            ]);
        }

        $show = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies/'.$movie->id);

        $show->assertOk()
            ->assertJsonPath('data.attributes.actors_count', 12);

        $credits = collect($show->json('data.attributes.credits'));
        $this->assertCount(10, $credits->where('profession.en_name', 'actor'));
        $this->assertCount(1, $credits->where('profession.en_name', 'director'));

        $all = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/movies/'.$movie->id.'/credits');

        $all->assertOk();
        $this->assertCount(13, $all->json('data'));
        $this->assertSame('movie_credits', $all->json('data.0.type'));
    }
}

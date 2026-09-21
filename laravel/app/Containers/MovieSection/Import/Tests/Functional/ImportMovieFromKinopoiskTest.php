<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tests\Functional;

use App\Containers\AppSection\Country\Models\Country;
use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Import\Enums\MovieImportStatusEnum;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportMovieFromKinopoiskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        config()->set('movie_import.token', 'test-api-key');
        config()->set('movie_import.base_url', 'https://api.poiskkino.dev');
        config()->set('movie_import.movie_path', '/v1.5/movie/%d');
        config()->set('movie_import.retries', 2);
        config()->set('movie_import.retry_sleep_ms', 0);
    }

    public function test_guest_cannot_import_movie(): void
    {
        $this->postJson('/api/v1/movie/imports', ['kp_id' => 325])->assertUnauthorized();
    }

    public function test_imports_movie_with_genres_countries_and_log(): void
    {
        Http::fake([
            'https://api.poiskkino.dev/v1.5/movie/325' => Http::response($this->moviePayload(), 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 325]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'movie_imports')
            ->assertJsonPath('data.attributes.status', MovieImportStatusEnum::Completed->value)
            ->assertJsonPath('data.attributes.kp_id', 325)
            ->assertJsonPath('data.attributes.was_created', true)
            ->assertJsonPath('data.attributes.source_url', 'https://api.poiskkino.dev/v1.5/movie/325')
            ->assertJsonPath('data.meta.source', 'poiskkino')
            ->assertJsonPath('data.meta.action', 'created')
            ->assertJsonPath('data.meta.duplicate', false)
            ->assertJsonPath('data.meta.after.title', 'Крестный отец')
            ->assertJsonPath('data.meta.after.short_description', 'Революция в гангстерском кино.')
            ->assertJsonPath('data.meta.after.id', $response->json('data.attributes.movie_id'));

        $this->assertNull($response->json('data.meta.before'));
        $this->assertDatabaseCount('movies', 1);

        $this->assertDatabaseHas('movies', [
            'kp_id' => 325,
            'title' => 'Крестный отец',
            'year' => 1972,
            'type' => MovieTypeEnum::MOVIE->value,
            'kp_img' => 'https://avatars.mds.yandex.net/get-kinopoisk-image/poster/600x900',
            'description' => 'Криминальная сага о семье Корлеоне.',
            'short_description' => 'Революция в гангстерском кино.',
        ]);
        $this->assertNotNull(Movie::query()->where('kp_id', 325)->value('kp_imported_at'));
        $includedMovie = collect($response->json('included'))->firstWhere('type', 'movies');
        $this->assertIsArray($includedMovie);
        $this->assertNotNull($includedMovie['attributes']['kp_imported_at'] ?? null);
        $this->assertDatabaseHas('movie_genres', ['name' => 'драма']);
        $this->assertDatabaseHas('countries', ['name' => 'США']);
        $this->assertDatabaseCount('movie_genre', 2);
        $this->assertDatabaseCount('movie_country', 1);
        $this->assertDatabaseHas('movie_persons', [
            'kp_id' => 7987,
            'name' => 'Аль Пачино',
            'en_name' => 'Al Pacino',
        ]);
        $this->assertDatabaseHas('movie_professions', [
            'en_name' => 'actor',
            'name' => 'актеры',
        ]);
        $this->assertDatabaseCount('movie_person', 2);
        $this->assertDatabaseHas('movie_imports', [
            'user_id' => $this->user->id,
            'kp_id' => 325,
            'status' => MovieImportStatusEnum::Completed->value,
        ]);
    }

    public function test_reimport_updates_existing_movie_and_keeps_custom_cover(): void
    {
        $movie = Movie::factory()->create([
            'kp_id' => 325,
            'title' => 'Old Title',
            'cover' => 'https://example.com/custom-cover.jpg',
            'kp_img' => 'https://example.com/old.jpg',
        ]);

        Http::fake([
            'https://api.poiskkino.dev/v1.5/movie/325' => Http::response($this->moviePayload(), 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 325]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.was_created', false)
            ->assertJsonPath('data.attributes.movie_id', $movie->id)
            ->assertJsonPath('data.meta.action', 'updated')
            ->assertJsonPath('data.meta.duplicate', true)
            ->assertJsonPath('data.meta.before.id', $movie->id)
            ->assertJsonPath('data.meta.before.title', 'Old Title')
            ->assertJsonPath('data.meta.before.kp_img', 'https://example.com/old.jpg')
            ->assertJsonPath('data.meta.after.id', $movie->id)
            ->assertJsonPath('data.meta.after.title', 'Крестный отец')
            ->assertJsonPath('data.meta.after.cover', 'https://example.com/custom-cover.jpg')
            ->assertJsonPath('data.meta.changes.title.old', 'Old Title')
            ->assertJsonPath('data.meta.changes.title.new', 'Крестный отец')
            ->assertJsonPath('data.meta.changes.kp_img.old', 'https://example.com/old.jpg')
            ->assertJsonPath('data.meta.changes.kp_img.new', 'https://avatars.mds.yandex.net/get-kinopoisk-image/poster/600x900');

        $this->assertDatabaseCount('movies', 1);

        $movie->refresh();
        $this->assertSame('Крестный отец', $movie->title);
        $this->assertSame('https://example.com/custom-cover.jpg', $movie->cover);
        $this->assertSame('https://avatars.mds.yandex.net/get-kinopoisk-image/poster/600x900', $movie->kp_img);
        $this->assertNotNull($movie->kp_imported_at);
    }

    public function test_missing_api_key_is_logged_as_failed(): void
    {
        config()->set('movie_import.token', '');
        Http::fake();

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 325]);

        $response->assertStatus(503)
            ->assertJsonPath('data.attributes.status', MovieImportStatusEnum::Failed->value)
            ->assertJsonPath('data.meta.reason', 'missing_api_key');

        $this->assertDatabaseMissing('movies', ['kp_id' => 325]);
        Http::assertNothingSent();
    }

    public function test_retries_after_connection_timeout_and_imports(): void
    {
        $attempts = 0;
        Http::fake(function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                throw new ConnectionException('cURL error 28: Connection timed out after 5000 milliseconds');
            }

            return Http::response($this->moviePayload(), 200);
        });

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 325])
            ->assertCreated()
            ->assertJsonPath('data.attributes.kp_id', 325);

        $this->assertSame(2, $attempts);
        $this->assertDatabaseHas('movies', ['kp_id' => 325, 'title' => 'Крестный отец']);
    }

    public function test_connection_timeout_is_logged_as_failed(): void
    {
        Http::fake(function () {
            throw new ConnectionException('cURL error 28: Connection timed out after 5000 milliseconds');
        });

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 325])
            ->assertStatus(504)
            ->assertJsonPath('data.attributes.status', MovieImportStatusEnum::Failed->value)
            ->assertJsonPath('data.meta.reason', 'connection_timeout');

        $this->assertDatabaseMissing('movies', ['kp_id' => 325]);
    }

    public function test_not_found_is_logged_as_failed(): void
    {
        Http::fake([
            'https://api.poiskkino.dev/v1.5/movie/325' => Http::response(['message' => 'Not found'], 404),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 325]);

        $response->assertNotFound()
            ->assertJsonPath('data.attributes.status', MovieImportStatusEnum::Failed->value)
            ->assertJsonPath('data.meta.reason', 'not_found')
            ->assertJsonPath('data.meta.kinopoisk.http_status', 404);

        $this->assertDatabaseMissing('movies', ['kp_id' => 325]);
    }

    public function test_invalid_payload_is_logged_as_failed(): void
    {
        Http::fake([
            'https://api.poiskkino.dev/v1.5/movie/325' => Http::response(['id' => 325, 'name' => null, 'year' => null], 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 325]);

        $response->assertStatus(422)
            ->assertJsonPath('data.attributes.status', MovieImportStatusEnum::Failed->value)
            ->assertJsonPath('data.meta.reason', 'missing_title')
            ->assertJsonPath('data.meta.exception.class', 'App\\Containers\\MovieSection\\Import\\Exceptions\\KinopoiskParseException');
    }

    public function test_user_can_list_and_get_own_import_history(): void
    {
        $drama = Genre::factory()->create(['name' => 'драма', 'slug' => 'drama']);
        $usa = Country::create(['name' => 'США']);
        $movie = Movie::factory()->create(['kp_id' => 325, 'title' => 'Крестный отец']);
        $movie->genres()->attach($drama);
        $movie->countries()->attach($usa);

        Http::fake([
            'https://api.poiskkino.dev/v1.5/movie/325' => Http::response($this->moviePayload(), 200),
        ]);

        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 325])
            ->assertCreated();

        $importId = (int) $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/imports')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/imports/'.$importId)
            ->assertOk()
            ->assertJsonPath('data.attributes.kp_id', 325);
    }

    /**
     * @return array<string, mixed>
     */
    private function moviePayload(): array
    {
        return [
            'id' => 325,
            'name' => 'Крестный отец',
            'alternativeName' => 'The Godfather',
            'year' => 1972,
            'type' => 'movie',
            'isSeries' => false,
            'description' => 'Криминальная сага о семье Корлеоне.',
            'shortDescription' => 'Революция в гангстерском кино.',
            'poster' => [
                'url' => 'https://avatars.mds.yandex.net/get-kinopoisk-image/poster/600x900',
                'previewUrl' => 'https://avatars.mds.yandex.net/get-kinopoisk-image/poster/300x450',
            ],
            'rating' => ['kp' => 8.709, 'imdb' => 9.2],
            'genres' => [
                ['id' => 8, 'name' => 'драма', 'slug' => 'drama'],
                ['id' => 16, 'name' => 'криминал', 'slug' => 'crime'],
            ],
            'countries' => [
                ['id' => 1, 'name' => 'США'],
            ],
            'persons' => [
                [
                    'id' => 7987,
                    'photo' => 'https://example.com/pacino.jpg',
                    'name' => 'Аль Пачино',
                    'enName' => 'Al Pacino',
                    'description' => 'Michael Corleone',
                    'profession' => 'актеры',
                    'enProfession' => 'actor',
                ],
                [
                    'id' => 24264,
                    'photo' => 'https://example.com/coppola.jpg',
                    'name' => 'Фрэнсис Форд Коппола',
                    'enName' => 'Francis Ford Coppola',
                    'profession' => 'режиссеры',
                    'enProfession' => 'director',
                ],
            ],
        ];
    }
}

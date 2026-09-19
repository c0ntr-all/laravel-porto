<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tests\Functional;

use App\Containers\AppSection\Country\Models\Country;
use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Import\Enums\MovieImportStatusEnum;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }

    public function test_guest_cannot_import_movie(): void
    {
        $this->postJson('/api/v1/movie/imports', ['kp_id' => 326])->assertUnauthorized();
    }

    public function test_imports_movie_with_genres_countries_and_log(): void
    {
        Http::fake([
            'https://www.kinopoisk.ru/film/326/' => Http::response($this->filmHtml(), 200),
            'https://www.kinopoisk.ru/series/326/' => Http::response('Not Found', 404),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 326]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'movie_imports')
            ->assertJsonPath('data.attributes.status', MovieImportStatusEnum::Completed->value)
            ->assertJsonPath('data.attributes.kp_id', 326)
            ->assertJsonPath('data.attributes.was_created', true);

        $this->assertDatabaseHas('movies', [
            'kp_id' => 326,
            'title' => 'Криминальное чтиво',
            'year' => 1994,
            'type' => MovieTypeEnum::MOVIE->value,
            'description' => 'Банда налетчиков устраивает ограбление.',
        ]);
        $this->assertDatabaseHas('movie_genres', ['name' => 'драма']);
        $this->assertDatabaseHas('countries', ['name' => 'США']);
        $this->assertDatabaseCount('movie_genre', 2);
        $this->assertDatabaseCount('movie_country', 1);
        $this->assertDatabaseHas('movie_imports', [
            'user_id' => $this->user->id,
            'kp_id' => 326,
            'status' => MovieImportStatusEnum::Completed->value,
        ]);
    }

    public function test_reimport_updates_existing_movie_and_keeps_custom_cover(): void
    {
        $movie = Movie::factory()->create([
            'kp_id' => 326,
            'title' => 'Old Title',
            'cover' => 'https://example.com/custom-cover.jpg',
            'kp_img' => 'https://example.com/old.jpg',
        ]);

        Http::fake([
            'https://www.kinopoisk.ru/film/326/' => Http::response($this->filmHtml(), 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 326]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.was_created', false);

        $movie->refresh();
        $this->assertSame('Криминальное чтиво', $movie->title);
        $this->assertSame('https://example.com/custom-cover.jpg', $movie->cover);
        $this->assertSame('https://avatars.mds.yandex.net/get-kinopoisk-image/cover/orig', $movie->kp_img);
    }

    public function test_blocked_page_is_logged_as_failed(): void
    {
        Http::fake([
            'www.kinopoisk.ru/*' => Http::response(
                '<html><body>var it = {"host":"https://sso.kinopoisk.ru/install"};showcaptcha</body></html>',
                200,
            ),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 326]);

        $response->assertStatus(503)
            ->assertJsonPath('data.attributes.status', MovieImportStatusEnum::Failed->value)
            ->assertJsonPath('data.meta.reason', 'blocked')
            ->assertJsonPath('data.meta.stage', 'fetch')
            ->assertJsonPath('data.meta.kinopoisk.signals.has_sso_install', true)
            ->assertJsonPath('data.meta.exception.message', 'Kinopoisk blocked the request with a captcha or SSO challenge.');

        $this->assertDatabaseMissing('movies', ['kp_id' => 326]);
        $this->assertDatabaseHas('movie_imports', [
            'kp_id' => 326,
            'status' => MovieImportStatusEnum::Failed->value,
        ]);
    }

    public function test_parse_failure_stores_kinopoisk_response_in_meta(): void
    {
        $html = '<html><head><title>Кинопоиск: доступ ограничен</title></head><body>Нет данных фильма</body></html>';

        Http::fake([
            'https://www.kinopoisk.ru/film/326/' => Http::response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']),
            'https://www.kinopoisk.ru/series/326/' => Http::response('Not Found', 404),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 326]);

        $response->assertStatus(422)
            ->assertJsonPath('data.attributes.status', MovieImportStatusEnum::Failed->value)
            ->assertJsonPath('data.attributes.http_status', 200)
            ->assertJsonPath('data.meta.reason', 'missing_title')
            ->assertJsonPath('data.meta.stage', 'parse')
            ->assertJsonPath('data.meta.kinopoisk.http_status', 200)
            ->assertJsonPath('data.meta.kinopoisk.page_title', 'Кинопоиск: доступ ограничен')
            ->assertJsonPath('data.meta.kinopoisk.signals.has_json_ld', false)
            ->assertJsonPath('data.meta.exception.class', 'App\\Containers\\MovieSection\\Import\\Exceptions\\KinopoiskParseException');

        $this->assertStringContainsString('Нет данных фильма', (string) $response->json('data.meta.kinopoisk.html_preview'));
    }

    public function test_user_can_list_and_get_own_import_history(): void
    {
        $drama = Genre::factory()->create(['name' => 'драма', 'slug' => 'drama']);
        $usa = Country::create(['name' => 'США']);
        $movie = Movie::factory()->create(['kp_id' => 326, 'title' => 'Криминальное чтиво']);
        $movie->genres()->attach($drama);
        $movie->countries()->attach($usa);

        Http::fake([
            'https://www.kinopoisk.ru/film/326/' => Http::response($this->filmHtml(), 200),
        ]);

        $created = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/imports', ['kp_id' => 326])
            ->assertCreated();

        $importId = (int) $created->json('data.id');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/imports')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/imports/'.$importId)
            ->assertOk()
            ->assertJsonPath('data.attributes.kp_id', 326);
    }

    private function filmHtml(): string
    {
        $json = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Movie',
            'name' => 'Криминальное чтиво',
            'image' => 'https://avatars.mds.yandex.net/get-kinopoisk-image/cover',
            'dateCreated' => '1994',
            'genre' => ['криминал', 'драма'],
            'countryOfOrigin' => [['name' => 'США']],
            'aggregateRating' => ['ratingValue' => '8.687'],
            'description' => 'Банда налетчиков устраивает ограбление.',
        ], JSON_UNESCAPED_UNICODE);

        return '<html><head><script type="application/ld+json">'.$json.'</script></head><body>film</body></html>';
    }
}

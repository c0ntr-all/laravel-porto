<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tests\Unit;

use App\Containers\MovieSection\Movie\Jobs\SyncMovieKinopoiskImagesJob;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\Tasks\PersistMovieKinopoiskImageTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyncMovieKinopoiskImagesJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Cache::flush();
    }

    public function test_processes_movies_sequentially_and_stores_cursor(): void
    {
        $first = Movie::factory()->create([
            'kp_img' => 'https://cdn.example.com/one.jpg',
            'img' => null,
        ]);
        $second = Movie::factory()->create([
            'kp_img' => 'https://cdn.example.com/two.jpg',
            'img' => null,
        ]);
        Movie::factory()->create([
            'kp_img' => null,
            'img' => null,
        ]);

        Http::fake([
            'https://cdn.example.com/*' => Http::response('img', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        (new SyncMovieKinopoiskImagesJob(resetCursor: true))->handle(
            app(PersistMovieKinopoiskImageTask::class),
        );

        $first->refresh();
        $second->refresh();

        $this->assertSame('kinopoisk/movies/'.$first->id.'/poster.jpg', $first->img);
        $this->assertSame('kinopoisk/movies/'.$second->id.'/poster.jpg', $second->img);
        Storage::disk('public')->assertExists($first->img);
        Storage::disk('public')->assertExists($second->img);

        $this->assertSame($second->id, (int) Cache::get(SyncMovieKinopoiskImagesJob::CACHE_LAST_ID));
        $this->assertSame('completed', Cache::get(SyncMovieKinopoiskImagesJob::CACHE_STATUS));

        $stats = Cache::get(SyncMovieKinopoiskImagesJob::CACHE_STATS);
        $this->assertIsArray($stats);
        $this->assertSame(2, $stats['processed']);
        $this->assertSame(2, $stats['succeeded']);
        $this->assertSame(0, $stats['failed']);
    }

    public function test_resumes_after_saved_cursor_and_continues_on_item_failure(): void
    {
        $first = Movie::factory()->create([
            'kp_img' => 'https://cdn.example.com/ok.jpg',
            'img' => null,
        ]);
        $broken = Movie::factory()->create([
            'kp_img' => 'https://cdn.example.com/broken.jpg',
            'img' => null,
        ]);
        $third = Movie::factory()->create([
            'kp_img' => 'https://cdn.example.com/again.jpg',
            'img' => null,
        ]);

        Cache::put(SyncMovieKinopoiskImagesJob::CACHE_LAST_ID, $first->id);

        Http::fake([
            'https://cdn.example.com/ok.jpg' => Http::response('ok', 200, ['Content-Type' => 'image/jpeg']),
            'https://cdn.example.com/broken.jpg' => Http::response('nope', 500),
            'https://cdn.example.com/again.jpg' => Http::response('ok', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        (new SyncMovieKinopoiskImagesJob())->handle(
            app(PersistMovieKinopoiskImageTask::class),
        );

        $first->refresh();
        $broken->refresh();
        $third->refresh();

        $this->assertNull($first->img);
        $this->assertNull($broken->img);
        $this->assertSame('kinopoisk/movies/'.$third->id.'/poster.jpg', $third->img);
        $this->assertSame($third->id, (int) Cache::get(SyncMovieKinopoiskImagesJob::CACHE_LAST_ID));

        $stats = Cache::get(SyncMovieKinopoiskImagesJob::CACHE_STATS);
        $this->assertSame(2, $stats['processed']);
        $this->assertSame(1, $stats['succeeded']);
        $this->assertSame(1, $stats['failed']);
    }
}

<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit;

use App\Ship\Data\DTO\PersistRemoteImageData;
use App\Ship\Tasks\PersistRemoteImageTask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class PersistRemoteImageTaskTest extends TestCase
{
    public function test_downloads_remote_image_into_configured_directory(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://cdn.example.com/poster.png' => Http::response(
                'png-bytes',
                200,
                ['Content-Type' => 'image/png'],
            ),
        ]);

        $path = app(PersistRemoteImageTask::class)->run(PersistRemoteImageData::from([
            'url' => 'https://cdn.example.com/poster.png',
            'directory' => 'kinopoisk/movies/42',
            'disk' => 'public',
            'filename' => 'poster',
        ]));

        $this->assertSame('kinopoisk/movies/42/poster.png', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('png-bytes', Storage::disk('public')->get($path));
    }

    public function test_throws_when_remote_image_is_not_found(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://cdn.example.com/missing.jpg' => Http::response('missing', 404),
        ]);

        $this->expectException(RuntimeException::class);

        app(PersistRemoteImageTask::class)->run(PersistRemoteImageData::from([
            'url' => 'https://cdn.example.com/missing.jpg',
            'directory' => 'kinopoisk/movies/1',
            'disk' => 'public',
            'filename' => 'poster',
        ]));
    }
}

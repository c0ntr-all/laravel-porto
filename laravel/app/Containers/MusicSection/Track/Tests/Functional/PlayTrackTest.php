<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlayTrackTest extends TestCase
{
    use RefreshDatabase;

    private string $libraryRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->libraryRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'music-library-' . uniqid('', true);
        mkdir($this->libraryRoot, 0777, true);

        config([
            'filesystems.disks.windows_f.root' => $this->libraryRoot,
            'music_upload.disk' => 'windows_f',
            'music_upload.drive' => 'F',
        ]);
        Storage::forgetDisk('windows_f');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->libraryRoot);
        parent::tearDown();
    }

    public function test_guest_cannot_play_track(): void
    {
        $track = $this->createTrackWithFile();

        $this->get("/api/v1/music/tracks/{$track->id}/play")
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_stream_full_track(): void
    {
        $user = User::factory()->create();
        $payload = str_repeat('A', 64);
        $track = $this->createTrackWithFile($payload);

        $response = $this->actingAs($user, 'api')
            ->get("/api/v1/music/tracks/{$track->id}/play");

        $response->assertOk();
        $this->assertSame('audio/mpeg', $response->headers->get('Content-Type'));
        $this->assertSame('bytes', $response->headers->get('Accept-Ranges'));
        $this->assertSame($payload, $response->streamedContent());
    }

    public function test_range_request_returns_partial_content(): void
    {
        $user = User::factory()->create();
        $payload = '0123456789';
        $track = $this->createTrackWithFile($payload);

        $response = $this->actingAs($user, 'api')
            ->withHeaders(['Range' => 'bytes=2-5'])
            ->get("/api/v1/music/tracks/{$track->id}/play");

        $response->assertStatus(206);
        $this->assertSame('bytes', $response->headers->get('Accept-Ranges'));
        $this->assertSame('bytes 2-5/10', $response->headers->get('Content-Range'));
        $this->assertSame('2345', $response->streamedContent());
    }

    public function test_head_request_returns_accept_ranges_without_body(): void
    {
        $user = User::factory()->create();
        $track = $this->createTrackWithFile(str_repeat('B', 32));

        $response = $this->actingAs($user, 'api')
            ->head("/api/v1/music/tracks/{$track->id}/play");

        $response->assertOk();
        $this->assertSame('bytes', $response->headers->get('Accept-Ranges'));
        $this->assertSame('audio/mpeg', $response->headers->get('Content-Type'));
        $this->assertSame('', $response->streamedContent());
    }

    public function test_missing_file_returns_not_found(): void
    {
        $user = User::factory()->create();
        $album = $this->createAlbum();
        $track = Track::create([
            'album_id' => $album->id,
            'name' => 'Ghost Track',
            'number' => 1,
            'path' => 'F:\\Missing\\ghost.mp3',
        ]);

        $this->actingAs($user, 'api')
            ->get("/api/v1/music/tracks/{$track->id}/play")
            ->assertNotFound();
    }

    public function test_path_outside_library_returns_not_found(): void
    {
        $user = User::factory()->create();
        $album = $this->createAlbum();
        $track = Track::create([
            'album_id' => $album->id,
            'name' => 'Outside Track',
            'number' => 1,
            'path' => 'D:\\Music\\outside.mp3',
        ]);

        $this->actingAs($user, 'api')
            ->get("/api/v1/music/tracks/{$track->id}/play")
            ->assertNotFound();
    }

    public function test_invalid_query_access_token_is_rejected(): void
    {
        $track = $this->createTrackWithFile('token-ok');

        $this->get("/api/v1/music/tracks/{$track->id}/play?access_token=invalid-token")
            ->assertUnauthorized();
    }

    private function createTrackWithFile(string $contents = 'audio-bytes'): Track
    {
        $relative = 'Artist' . DIRECTORY_SEPARATOR . 'Album' . DIRECTORY_SEPARATOR . 'track.mp3';
        $absolute = $this->libraryRoot . DIRECTORY_SEPARATOR . $relative;
        mkdir(dirname($absolute), 0777, true);
        file_put_contents($absolute, $contents);

        $album = $this->createAlbum();

        return Track::create([
            'album_id' => $album->id,
            'name' => 'Playable Track',
            'number' => 1,
            'path' => 'F:\\Artist\\Album\\track.mp3',
        ]);
    }

    private function createAlbum(): Album
    {
        return Album::create([
            'name' => 'Test Album',
            'path' => 'F:\\Artist\\Album',
            'album_type_id' => 1,
        ]);
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($directory);
    }
}

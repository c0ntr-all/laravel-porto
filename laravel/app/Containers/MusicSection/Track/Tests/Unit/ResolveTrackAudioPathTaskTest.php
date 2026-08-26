<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tests\Unit;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\Tasks\ResolveTrackAudioPathTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ResolveTrackAudioPathTaskTest extends TestCase
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

    public function test_it_resolves_windows_path_inside_the_library(): void
    {
        $absolute = $this->libraryRoot . DIRECTORY_SEPARATOR . 'Artist' . DIRECTORY_SEPARATOR . 'song.mp3';
        mkdir(dirname($absolute), 0777, true);
        file_put_contents($absolute, 'bytes');

        $track = $this->makeTrack('F:\\Artist\\song.mp3');

        $resolved = app(ResolveTrackAudioPathTask::class)->run($track);

        $this->assertSame(realpath($absolute), $resolved);
    }

    public function test_it_rejects_path_escape_outside_the_library(): void
    {
        $outside = dirname($this->libraryRoot) . DIRECTORY_SEPARATOR . 'outside-track-' . uniqid('', true) . '.mp3';
        file_put_contents($outside, 'secret');

        $track = $this->makeTrack('F:\\..\\' . basename($outside));

        $this->expectException(NotFoundHttpException::class);

        try {
            app(ResolveTrackAudioPathTask::class)->run($track);
        } finally {
            @unlink($outside);
        }
    }

    public function test_it_rejects_empty_path(): void
    {
        $track = $this->makeTrack(null);

        $this->expectException(NotFoundHttpException::class);

        app(ResolveTrackAudioPathTask::class)->run($track);
    }

    private function makeTrack(?string $path): Track
    {
        $album = Album::create([
            'name' => 'Album',
            'path' => 'F:\\Artist',
            'album_type_id' => 1,
        ]);

        return Track::create([
            'album_id' => $album->id,
            'name' => 'Track',
            'number' => 1,
            'path' => $path,
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

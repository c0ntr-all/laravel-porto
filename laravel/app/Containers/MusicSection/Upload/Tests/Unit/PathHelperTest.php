<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class PathHelperTest extends TestCase
{
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
            'music_upload.root_path' => 'F:\\Music',
        ]);
        Storage::forgetDisk('windows_f');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->libraryRoot);
        parent::tearDown();
    }

    public function test_it_normalizes_windows_paths(): void
    {
        $this->assertSame(
            'F:\\Music\\Metallica',
            PathHelper::normalizeWindows('F:/Music/Metallica/')
        );
    }

    public function test_it_round_trips_windows_and_linux_paths(): void
    {
        $windows = 'F:\\Music\\Metal\\Metallica';

        $linux = PathHelper::toLinux($windows);
        $normalizedLinux = str_replace('\\', '/', $linux);

        $this->assertStringEndsWith('Music/Metal/Metallica', $normalizedLinux);
        $this->assertSame($windows, PathHelper::toWindows($linux));
    }

    public function test_it_rejects_relative_paths(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PathHelper::normalizeWindows('Music\\Metallica');
    }

    public function test_it_rejects_unmounted_drives(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PathHelper::toLinux('D:\\Music\\Metallica');
    }

    public function test_basename_and_dirname(): void
    {
        $this->assertSame('Metallica', PathHelper::basename('F:\\Music\\Metallica'));
        $this->assertSame('F:\\Music', PathHelper::dirname('F:\\Music\\Metallica'));
    }

    public function test_it_resolves_directories_inside_library_root(): void
    {
        $music = $this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'Metallica';
        mkdir($music, 0777, true);

        $this->assertTrue(PathHelper::isInsideLibrary('F:/Music/Metallica'));
        $this->assertFalse(PathHelper::isInsideLibrary('F:\\Images'));
        $this->assertStringEndsWith('Music' . DIRECTORY_SEPARATOR . 'Metallica', PathHelper::resolveLibraryDirectory('F:\\Music\\Metallica'));
    }

    public function test_it_rejects_path_traversal_outside_library_root(): void
    {
        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Music', 0777, true);
        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Images', 0777, true);

        $this->expectException(InvalidArgumentException::class);

        PathHelper::resolveLibraryDirectory('F:\\Music\\..\\Images');
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

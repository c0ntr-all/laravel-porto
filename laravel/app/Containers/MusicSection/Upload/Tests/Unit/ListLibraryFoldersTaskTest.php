<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Upload\Tasks\ListLibraryFoldersTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ListLibraryFoldersTaskTest extends TestCase
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
            'music_upload.root_path' => 'F:\\Music',
        ]);
        Storage::forgetDisk('windows_f');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->libraryRoot);
        parent::tearDown();
    }

    public function test_it_lists_immediate_children_and_marks_uploaded_artists(): void
    {
        $this->seedLibrary();

        $user = User::factory()->create();
        Artist::query()->create([
            'user_id' => $user->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);

        $folders = app(ListLibraryFoldersTask::class)->run('F:\\Music');
        $byName = collect($folders)->keyBy('name');

        $this->assertSame(['Alcest', 'Metallica', 'Unuploaded'], collect($folders)->pluck('name')->all());
        $this->assertTrue($byName['Metallica']->uploaded);
        $this->assertTrue($byName['Metallica']->has_children);
        $this->assertFalse($byName['Unuploaded']->uploaded);
        $this->assertFalse($byName['Alcest']->has_children);
        $this->assertArrayNotHasKey('FLAC', $byName->all());
    }

    public function test_it_lists_nested_album_folders_without_artist_checkmarks(): void
    {
        $this->seedLibrary();

        $user = User::factory()->create();
        Artist::query()->create([
            'user_id' => $user->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);

        $folders = app(ListLibraryFoldersTask::class)->run('F:\\Music\\Metallica');

        $this->assertCount(1, $folders);
        $this->assertSame('Black Album', $folders[0]->name);
        $this->assertFalse($folders[0]->uploaded);
        $this->assertFalse($folders[0]->has_children);
    }

    public function test_it_marks_uploaded_album_folders(): void
    {
        $this->seedLibrary();

        $user = User::factory()->create();
        Artist::query()->create([
            'user_id' => $user->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);
        Album::query()->create([
            'name' => 'Black Album',
            'path' => 'F:\\Music\\Metallica\\Black Album',
            'album_type_id' => 1,
        ]);

        $folders = app(ListLibraryFoldersTask::class)->run('F:\\Music\\Metallica');

        $this->assertCount(1, $folders);
        $this->assertSame('Black Album', $folders[0]->name);
        $this->assertTrue($folders[0]->uploaded);
        $this->assertFalse($folders[0]->has_children);
    }

    private function seedLibrary(): void
    {
        $metallicaAlbum = $this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'Metallica' . DIRECTORY_SEPARATOR . 'Black Album';
        mkdir($metallicaAlbum, 0777, true);
        file_put_contents($metallicaAlbum . DIRECTORY_SEPARATOR . '01. Enter Sandman.mp3', 'fake-audio');

        $unuploaded = $this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'Unuploaded';
        mkdir($unuploaded, 0777, true);

        $alcest = $this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'Alcest';
        mkdir($alcest, 0777, true);
        file_put_contents($alcest . DIRECTORY_SEPARATOR . '01. Track.mp3', 'fake-audio');

        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'FLAC', 0777, true);
        file_put_contents($this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'readme.txt', 'skip');
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

<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListLibraryFoldersTest extends TestCase
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
        $this->seedLibrary();
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->libraryRoot);
        parent::tearDown();
    }

    public function test_guest_cannot_list_library_folders(): void
    {
        $this->getJson('/api/v1/music/library/folders')->assertUnauthorized();
    }

    public function test_non_admin_cannot_list_library_folders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/library/folders')
            ->assertForbidden();
    }

    public function test_admin_lists_music_root_and_marks_uploaded_artists(): void
    {
        $admin = $this->makeAdmin();
        Artist::query()->create([
            'user_id' => $admin->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/v1/music/library/folders')
            ->assertOk()
            ->assertJsonPath('meta.path', 'F:\\Music')
            ->assertJsonPath('meta.name', 'Music')
            ->assertJsonPath('meta.uploaded', false);

        $names = collect($response->json('data'))->pluck('attributes.name')->all();
        $this->assertSame(['Alcest', 'Metallica', 'Unuploaded'], $names);

        $metallica = collect($response->json('data'))->firstWhere('attributes.name', 'Metallica');
        $this->assertTrue($metallica['attributes']['uploaded']);
        $this->assertTrue($metallica['attributes']['has_children']);
        $this->assertSame('F:\\Music\\Metallica', $metallica['attributes']['path']);
        $this->assertFalse(
            collect($response->json('data'))->contains(fn (array $item) => $item['attributes']['name'] === 'FLAC')
        );
    }

    public function test_admin_lazy_loads_artist_subfolders(): void
    {
        $admin = $this->makeAdmin();
        Artist::query()->create([
            'user_id' => $admin->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/music/library/folders?path=' . urlencode('F:\\Music\\Metallica'))
            ->assertOk()
            ->assertJsonPath('meta.path', 'F:\\Music\\Metallica')
            ->assertJsonPath('meta.uploaded', true)
            ->assertJsonPath('data.0.attributes.name', 'Black Album')
            ->assertJsonPath('data.0.attributes.uploaded', false)
            ->assertJsonPath('data.0.attributes.has_children', false);
    }

    public function test_admin_marks_uploaded_album_folders(): void
    {
        $admin = $this->makeAdmin();
        Artist::query()->create([
            'user_id' => $admin->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);
        Album::query()->create([
            'name' => 'Black Album',
            'path' => 'F:\\Music\\Metallica\\Black Album',
            'album_type_id' => 1,
        ]);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/music/library/folders?path=' . urlencode('F:\\Music\\Metallica'))
            ->assertOk()
            ->assertJsonPath('meta.uploaded', true)
            ->assertJsonPath('data.0.attributes.name', 'Black Album')
            ->assertJsonPath('data.0.attributes.uploaded', true)
            ->assertJsonPath('data.0.attributes.path', 'F:\\Music\\Metallica\\Black Album');
    }

    public function test_admin_cannot_list_folder_outside_library_root(): void
    {
        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Images', 0777, true);

        $this->actingAs($this->makeAdmin(), 'api')
            ->getJson('/api/v1/music/library/folders?path=' . urlencode('F:\\Images'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['path']);
    }

    public function test_admin_cannot_list_missing_folder(): void
    {
        $this->actingAs($this->makeAdmin(), 'api')
            ->getJson('/api/v1/music/library/folders?path=' . urlencode('F:\\Music\\DoesNotExist'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['path']);
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('admin', 'api');
        $user->assignRole($role);

        return $user;
    }

    private function seedLibrary(): void
    {
        $metallicaAlbum = $this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'Metallica' . DIRECTORY_SEPARATOR . 'Black Album';
        mkdir($metallicaAlbum, 0777, true);
        file_put_contents($metallicaAlbum . DIRECTORY_SEPARATOR . '01. Enter Sandman.mp3', 'fake-audio');

        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'Unuploaded', 0777, true);
        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'Alcest', 0777, true);
        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'FLAC', 0777, true);
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

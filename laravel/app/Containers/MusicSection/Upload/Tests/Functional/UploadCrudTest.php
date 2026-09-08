<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Upload\Enums\UploadStatusEnum;
use App\Containers\MusicSection\Upload\Enums\UploadTrackStatusEnum;
use App\Containers\MusicSection\Upload\Jobs\ImportArtistMusicJob;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Containers\MusicSection\Upload\Support\Id3Reader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UploadCrudTest extends TestCase
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
        Event::fake();
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->libraryRoot);
        parent::tearDown();
    }

    public function test_guest_cannot_create_upload(): void
    {
        $this->postJson('/api/v1/music/uploads', [
            'path' => 'F:\\Music\\Metallica',
        ])->assertUnauthorized();
    }

    public function test_non_admin_cannot_create_upload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/uploads', [
                'path' => 'F:\\Music\\Metallica',
            ])
            ->assertForbidden();
    }

    public function test_admin_cannot_create_upload_without_path(): void
    {
        $this->actingAs($this->makeAdmin(), 'api')
            ->postJson('/api/v1/music/uploads', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['path']);
    }

    public function test_admin_cannot_import_missing_folder(): void
    {
        $this->actingAs($this->makeAdmin(), 'api')
            ->postJson('/api/v1/music/uploads', [
                'path' => 'F:\\Music\\DoesNotExist',
            ])
            ->assertUnprocessable();
    }

    public function test_admin_can_list_and_delete_upload_without_touching_catalog(): void
    {
        $admin = $this->makeAdmin();
        $artist = Artist::create([
            'user_id' => $admin->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);
        $album = Album::create([
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica\\Metallica',
            'album_type_id' => 1,
        ]);
        Track::create([
            'album_id' => $album->id,
            'name' => 'Enter Sandman',
            'path' => 'F:\\Music\\Metallica\\Metallica\\01. Enter Sandman.mp3',
            'number' => 1,
        ]);

        $upload = MusicUpload::create([
            'user_id' => $admin->id,
            'source_path' => 'F:\\Music\\Metallica',
            'status' => UploadStatusEnum::Completed,
        ]);
        $upload->artists()->attach($artist->id);
        $upload->albums()->attach($album->id);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/music/uploads')
            ->assertOk()
            ->assertJsonPath('data.0.id', (string) $upload->id)
            ->assertJsonPath('data.0.attributes.artist_ids', [$artist->id])
            ->assertJsonPath('data.0.attributes.artist_name', 'Metallica')
            ->assertJsonPath('data.0.attributes.album_ids', [$album->id])
            ->assertJsonCount(1, 'data.0.relationships.artists.data');

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/music/uploads/' . $upload->id)
            ->assertOk()
            ->assertJsonPath('data.id', (string) $upload->id)
            ->assertJsonPath('data.attributes.artist_ids', [$artist->id])
            ->assertJsonCount(1, 'data.relationships.artists.data')
            ->assertJsonCount(1, 'data.relationships.albums.data');

        $this->actingAs($admin, 'api')
            ->deleteJson('/api/v1/music/uploads/' . $upload->id)
            ->assertOk();

        $this->assertDatabaseMissing('music_uploads', ['id' => $upload->id]);
        $this->assertDatabaseHas('music_artists', ['id' => $artist->id]);
        $this->assertDatabaseHas('music_tracks', ['name' => 'Enter Sandman']);
    }

    public function test_admin_can_import_artist_folder_and_skip_unchanged_reimport(): void
    {
        $this->mockId3Reader();
        $this->seedArtistFolder();

        $admin = $this->makeAdmin();

        $first = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/music/uploads', [
                'path' => 'F:\\Music\\Metallica',
            ]);

        $first->assertCreated();
        $first->assertJsonPath('data.attributes.status', UploadStatusEnum::Completed->value);
        $first->assertJsonCount(1, 'data.relationships.artists.data');
        $first->assertJsonCount(1, 'data.relationships.albums.data');
        $first->assertJsonCount(1, 'data.relationships.tracks.data');
        $this->assertDatabaseCount('music_artists', 1);
        $this->assertDatabaseCount('music_tracks', 1);
        $this->assertDatabaseCount('music_upload_artist', 1);
        $this->assertDatabaseCount('music_upload_album', 1);

        $second = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/music/uploads', [
                'path' => 'F:\\Music\\Metallica',
            ]);

        $second->assertCreated();
        $second->assertJsonPath('data.attributes.tracks_skipped', 1);
        $second->assertJsonPath('data.attributes.tracks_created', 0);
        $this->assertDatabaseCount('music_artists', 1);
        $this->assertDatabaseCount('music_albums', 1);
        $this->assertDatabaseCount('music_tracks', 1);
        $this->assertDatabaseCount('music_uploads', 2);
        $this->assertDatabaseCount('music_upload_artist', 2);
        $this->assertDatabaseCount('music_upload_album', 2);
    }

    public function test_admin_create_upload_queues_import_job(): void
    {
        Queue::fake();
        $this->seedArtistFolder();

        $this->actingAs($this->makeAdmin(), 'api')
            ->postJson('/api/v1/music/uploads', [
                'path' => 'F:\\Music\\Metallica',
            ])
            ->assertCreated()
            ->assertJsonPath('data.attributes.status', UploadStatusEnum::Pending->value);

        Queue::assertPushed(ImportArtistMusicJob::class);
    }

    public function test_admin_can_inspect_all_artists_albums_and_tracks_of_a_session(): void
    {
        $admin = $this->makeAdmin();
        $alcest = Artist::create([
            'user_id' => $admin->id,
            'name' => 'Alcest',
            'path' => 'F:\\Music\\Alcest',
        ]);
        $angmar = Artist::create([
            'user_id' => $admin->id,
            'name' => 'Angmar',
            'path' => 'F:\\Music\\Alcest :: Angmar',
        ]);
        $discrets = Artist::create([
            'user_id' => $admin->id,
            'name' => 'Les Discrets',
            'path' => 'F:\\Music\\Alcest :: Les Discrets',
        ]);
        $albumA = Album::create([
            'name' => 'Écailles de Lune',
            'path' => 'F:\\Music\\Alcest\\Écailles de Lune',
            'album_type_id' => 1,
        ]);
        $albumB = Album::create([
            'name' => 'Septembre Et Ses Dernières Pensées',
            'path' => 'F:\\Music\\Alcest\\Septembre',
            'album_type_id' => 1,
        ]);
        $track = Track::create([
            'album_id' => $albumA->id,
            'name' => 'Percées De Lumière',
            'path' => 'F:\\Music\\Alcest\\Écailles de Lune\\02. Percées De Lumière.mp3',
            'number' => 2,
        ]);

        $upload = MusicUpload::create([
            'user_id' => $admin->id,
            'source_path' => 'F:\\Music\\Alcest',
            'status' => UploadStatusEnum::Completed,
        ]);
        $upload->artists()->attach([$alcest->id, $angmar->id, $discrets->id]);
        $upload->albums()->attach([$albumA->id, $albumB->id]);
        $upload->tracks()->create([
            'track_id' => $track->id,
            'album_id' => $albumA->id,
            'artist_id' => $alcest->id,
            'album_name' => $albumA->name,
            'track_name' => $track->name,
            'source_path' => $track->path,
            'status' => UploadTrackStatusEnum::Created,
        ]);

        $detail = $this->actingAs($admin, 'api')
            ->getJson('/api/v1/music/uploads/' . $upload->id)
            ->assertOk()
            ->assertJsonCount(3, 'data.relationships.artists.data')
            ->assertJsonCount(2, 'data.relationships.albums.data')
            ->assertJsonCount(1, 'data.relationships.tracks.data');

        $this->assertEqualsCanonicalizing(
            [$alcest->id, $angmar->id, $discrets->id],
            $detail->json('data.attributes.artist_ids'),
        );
        $this->assertEqualsCanonicalizing(
            [$albumA->id, $albumB->id],
            $detail->json('data.attributes.album_ids'),
        );

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/music/uploads?filter[artist_id]=' . $angmar->id)
            ->assertOk()
            ->assertJsonPath('data.0.id', (string) $upload->id);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/music/uploads/' . $upload->id . '/tracks')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.album_id', $albumA->id)
            ->assertJsonPath('data.0.attributes.artist_id', $alcest->id)
            ->assertJsonPath('data.0.relationships.artist.data.id', (string) $alcest->id)
            ->assertJsonPath('data.0.relationships.album.data.id', (string) $albumA->id);
    }

    public function test_admin_can_preview_artist_folder_without_persisting(): void
    {
        $this->mockId3Reader();
        $this->seedArtistFolder();

        $this->actingAs($this->makeAdmin(), 'api')
            ->postJson('/api/v1/music/uploads/previews', [
                'path' => 'F:\\Music\\Metallica',
            ])
            ->assertOk()
            ->assertJsonPath('data.artist.name', 'Metallica');

        $this->assertDatabaseCount('music_artists', 0);
        $this->assertDatabaseCount('music_uploads', 0);
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('admin', 'api');
        $user->assignRole($role);

        return $user;
    }

    private function seedArtistFolder(): void
    {
        $albumDir = $this->libraryRoot . DIRECTORY_SEPARATOR . 'Music' . DIRECTORY_SEPARATOR . 'Metallica' . DIRECTORY_SEPARATOR . 'Metallica';
        mkdir($albumDir, 0777, true);
        file_put_contents($albumDir . DIRECTORY_SEPARATOR . '01. Enter Sandman.mp3', 'fake-audio');
    }

    private function mockId3Reader(): void
    {
        $this->mock(Id3Reader::class, function ($mock) {
            $mock->shouldReceive('read')->andReturn([
                'comments' => [
                    'title' => ['Enter Sandman'],
                    'album' => ['Metallica'],
                    'albumartist' => ['Metallica'],
                    'artist' => ['Metallica'],
                    'year' => ['1991'],
                    'track_number' => ['1'],
                    'genre' => ['Metal'],
                ],
                'playtime_seconds' => 331.2,
                'audio' => [
                    'bitrate' => 320000,
                ],
            ]);
        });
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

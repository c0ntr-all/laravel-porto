<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Containers\MusicSection\Upload\Enums\UploadStatusEnum;
use App\Containers\MusicSection\Upload\Enums\UploadTrackStatusEnum;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Containers\MusicSection\Upload\Tasks\PersistLibraryTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PersistLibraryTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_catalog_records_on_first_import(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $upload = $this->makeSession($user);
        $tree = $this->makeTree();

        [, $counters] = app(PersistLibraryTask::class)->run($upload, $tree, $user->id);

        $this->assertSame(1, $counters['artists_created']);
        $this->assertSame(1, $counters['albums_created']);
        $this->assertSame(1, $counters['tracks_created']);
        $this->assertSame(0, $counters['tracks_skipped']);

        $this->assertDatabaseCount('music_artists', 1);
        $this->assertDatabaseCount('music_albums', 1);
        $this->assertDatabaseCount('music_tracks', 1);
        $this->assertDatabaseHas('music_artists', [
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);
        $this->assertDatabaseHas('music_tracks', [
            'name' => 'Enter Sandman',
            'path' => 'F:\\Music\\Metallica\\Metallica\\01. Enter Sandman.mp3',
        ]);
        $this->assertDatabaseHas('music_upload_tracks', [
            'upload_id' => $upload->id,
            'status' => UploadTrackStatusEnum::Created->value,
        ]);

        $upload->refresh();
        $this->assertNotNull($upload->artist_id);
        $this->assertSame('Metallica', $upload->artist_name);
    }

    public function test_it_skips_unchanged_tracks_on_reimport(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $first = $this->makeSession($user);
        $tree = $this->makeTree();

        app(PersistLibraryTask::class)->run($first, $tree, $user->id);

        $second = $this->makeSession($user);
        [, $counters] = app(PersistLibraryTask::class)->run($second, $tree, $user->id);

        $this->assertSame(0, $counters['artists_created']);
        $this->assertSame(0, $counters['albums_created']);
        $this->assertSame(0, $counters['tracks_created']);
        $this->assertSame(1, $counters['tracks_skipped']);

        $this->assertDatabaseCount('music_artists', 1);
        $this->assertDatabaseCount('music_albums', 1);
        $this->assertDatabaseCount('music_tracks', 1);
        $this->assertDatabaseHas('music_upload_tracks', [
            'upload_id' => $second->id,
            'status' => UploadTrackStatusEnum::Skipped->value,
        ]);
    }

    public function test_it_updates_track_when_metadata_changes(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $first = $this->makeSession($user);
        app(PersistLibraryTask::class)->run($first, $this->makeTree(), $user->id);

        $changed = $this->makeTree(title: 'Enter Sandman (Remastered)');
        $second = $this->makeSession($user);
        [, $counters] = app(PersistLibraryTask::class)->run($second, $changed, $user->id);

        $this->assertSame(1, $counters['tracks_updated']);
        $this->assertSame(0, $counters['tracks_created']);
        $this->assertDatabaseCount('music_tracks', 1);
        $this->assertDatabaseHas('music_tracks', ['name' => 'Enter Sandman (Remastered)']);
        $this->assertDatabaseHas('music_upload_tracks', [
            'upload_id' => $second->id,
            'status' => UploadTrackStatusEnum::Updated->value,
        ]);
    }

    private function makeSession(User $user): MusicUpload
    {
        return MusicUpload::create([
            'user_id' => $user->id,
            'source_path' => 'F:\\Music\\Metallica',
            'status' => UploadStatusEnum::Running,
        ]);
    }

    private function makeTree(string $title = 'Enter Sandman'): array
    {
        $track = ParsedTrackDto::from([
            'linux_path' => '/tmp/01. Enter Sandman.mp3',
            'windows_path' => 'F:\\Music\\Metallica\\Metallica\\01. Enter Sandman.mp3',
            'title' => $title,
            'album' => 'Metallica',
            'artist' => 'Metallica',
            'genre' => null,
            'year' => '1991',
            'date' => '1991-08-12',
            'track_number' => 1,
            'disc_number' => 1,
            'duration' => '00:05:31',
            'bitrate' => 320,
            'album_cover_linux_path' => null,
            'album_windows_path' => 'F:\\Music\\Metallica\\Metallica',
            'album_version' => null,
            'original_album' => null,
            'album_type_id' => 1,
        ]);

        return [
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
            'albums' => [[
                'name' => 'Metallica',
                'date' => '1991-08-12',
                'path' => 'F:\\Music\\Metallica\\Metallica',
                'album_type_id' => 1,
                'original_album' => null,
                'attributes' => null,
                'image' => null,
                'tracks' => [$track],
            ]],
        ];
    }
}

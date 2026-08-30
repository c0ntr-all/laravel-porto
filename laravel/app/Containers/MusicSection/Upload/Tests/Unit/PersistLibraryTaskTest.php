<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
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

        $counters = app(PersistLibraryTask::class)->run($upload, $tree, $user->id);

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
        $this->assertEquals(['Metallica'], $upload->artists()->pluck('name')->all());
        $this->assertEquals(['Metallica'], $upload->albums()->pluck('name')->all());
        $this->assertDatabaseCount('music_upload_artist', 1);
        $this->assertDatabaseCount('music_upload_album', 1);
        $this->assertDatabaseHas('music_upload_tracks', [
            'upload_id' => $upload->id,
            'status' => UploadTrackStatusEnum::Created->value,
            'album_id' => $upload->albums()->first()?->id,
            'artist_id' => $upload->artists()->first()?->id,
        ]);
    }

    public function test_it_skips_unchanged_tracks_on_reimport(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $first = $this->makeSession($user);
        $tree = $this->makeTree();

        app(PersistLibraryTask::class)->run($first, $tree, $user->id);

        $second = $this->makeSession($user);
        $counters = app(PersistLibraryTask::class)->run($second, $tree, $user->id);

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
        $counters = app(PersistLibraryTask::class)->run($second, $changed, $user->id);

        $this->assertSame(1, $counters['tracks_updated']);
        $this->assertSame(0, $counters['tracks_created']);
        $this->assertDatabaseCount('music_tracks', 1);
        $this->assertDatabaseHas('music_tracks', ['name' => 'Enter Sandman (Remastered)']);
        $this->assertDatabaseHas('music_upload_tracks', [
            'upload_id' => $second->id,
            'status' => UploadTrackStatusEnum::Updated->value,
        ]);
    }

    public function test_it_creates_two_artists_for_a_split_album(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $upload = $this->makeSession($user, 'F:\\Music\\In Tongues We Speak');
        $splitTypeId = (int) \App\Containers\MusicSection\Album\Models\AlbumType::query()
            ->where('slug', 'split')
            ->value('id');

        $tree = [
            'name' => 'In Tongues We Speak',
            'path' => 'F:\\Music\\In Tongues We Speak',
            'albums' => [[
                'name' => 'In Tongues We Speak',
                'date' => '1997-01-01',
                'path' => 'F:\\Music\\In Tongues We Speak',
                'album_type_id' => $splitTypeId ?: 1,
                'original_album' => null,
                'attributes' => null,
                'image' => null,
                'artists' => ['Napalm Death', 'Coalesce'],
                'tracks' => [
                    $this->makeTrackDto(
                        title: 'Food Chain',
                        album: 'In Tongues We Speak',
                        artist: 'Napalm Death',
                        windowsPath: 'F:\\Music\\In Tongues We Speak\\01. Food Chain.mp3',
                        albumPath: 'F:\\Music\\In Tongues We Speak',
                        number: 1,
                    ),
                    $this->makeTrackDto(
                        title: 'A New Language',
                        album: 'In Tongues We Speak',
                        artist: 'Coalesce',
                        windowsPath: 'F:\\Music\\In Tongues We Speak\\02. A New Language.mp3',
                        albumPath: 'F:\\Music\\In Tongues We Speak',
                        number: 2,
                    ),
                ],
            ]],
        ];

        $counters = app(PersistLibraryTask::class)->run($upload, $tree, $user->id);

        $this->assertSame(2, $counters['artists_created']);
        $this->assertSame(1, $counters['albums_created']);
        $this->assertSame(2, $counters['tracks_created']);
        $this->assertDatabaseCount('music_artists', 2);
        $this->assertDatabaseMissing('music_artists', ['name' => 'In Tongues We Speak']);
        $this->assertDatabaseHas('music_artists', ['name' => 'Napalm Death']);
        $this->assertDatabaseHas('music_artists', ['name' => 'Coalesce']);

        $album = \App\Containers\MusicSection\Album\Models\Album::query()->first();
        $this->assertNotNull($album);
        $this->assertEqualsCanonicalizing(
            ['Napalm Death', 'Coalesce'],
            $album->artists()->get()->pluck('name')->all(),
        );

        $napalmTrack = \App\Containers\MusicSection\Track\Models\Track::query()->where('name', 'Food Chain')->first();
        $coalesceTrack = \App\Containers\MusicSection\Track\Models\Track::query()->where('name', 'A New Language')->first();
        $this->assertEquals(['Napalm Death'], $napalmTrack?->artists()->get()->pluck('name')->all());
        $this->assertEquals(['Coalesce'], $coalesceTrack?->artists()->get()->pluck('name')->all());

        $upload->refresh();
        $this->assertEqualsCanonicalizing(
            ['Napalm Death', 'Coalesce'],
            $upload->artists()->pluck('name')->all(),
        );
        $this->assertEquals(['In Tongues We Speak'], $upload->albums()->pluck('name')->all());
        $this->assertDatabaseCount('music_upload_artist', 2);
        $this->assertDatabaseCount('music_upload_album', 1);
    }

    public function test_it_links_a_remaster_as_a_version_of_the_original_album(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $upload = $this->makeSession($user);
        $tree = [
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
            'albums' => [
                [
                    'name' => 'Master Of Puppets (Remastered)',
                    'date' => '2017-01-01',
                    'path' => 'F:\\Music\\Metallica\\Master Of Puppets Remastered',
                    'album_type_id' => 1,
                    'original_album' => 'Master Of Puppets',
                    'attributes' => 'Remastered',
                    'image' => null,
                    'artists' => ['Metallica'],
                    'tracks' => [
                        $this->makeTrackDto(
                            title: 'Battery',
                            album: 'Master Of Puppets (Remastered)',
                            artist: 'Metallica',
                            windowsPath: 'F:\\Music\\Metallica\\Master Of Puppets Remastered\\01. Battery.mp3',
                            albumPath: 'F:\\Music\\Metallica\\Master Of Puppets Remastered',
                            number: 1,
                        ),
                    ],
                ],
                [
                    'name' => 'Master Of Puppets',
                    'date' => '1986-03-03',
                    'path' => 'F:\\Music\\Metallica\\Master Of Puppets',
                    'album_type_id' => 1,
                    'original_album' => null,
                    'attributes' => null,
                    'image' => null,
                    'artists' => ['Metallica'],
                    'tracks' => [
                        $this->makeTrackDto(
                            title: 'Battery',
                            album: 'Master Of Puppets',
                            artist: 'Metallica',
                            windowsPath: 'F:\\Music\\Metallica\\Master Of Puppets\\01. Battery.mp3',
                            albumPath: 'F:\\Music\\Metallica\\Master Of Puppets',
                            number: 1,
                        ),
                    ],
                ],
            ],
        ];

        app(PersistLibraryTask::class)->run($upload, $tree, $user->id);

        $original = Album::query()->where('name', 'Master Of Puppets')->whereNull('parent_id')->first();
        $remaster = Album::query()->where('name', 'Master Of Puppets (Remastered)')->first();

        $this->assertNotNull($original);
        $this->assertNotNull($remaster);
        $this->assertSame($original->id, $remaster->parent_id);
        $this->assertSame('Remastered', $remaster->edition);
        $this->assertCount(1, $original->versions);
    }

    private function makeSession(User $user, string $sourcePath = 'F:\\Music\\Metallica'): MusicUpload
    {
        return MusicUpload::create([
            'user_id' => $user->id,
            'source_path' => $sourcePath,
            'status' => UploadStatusEnum::Running,
        ]);
    }

    private function makeTree(string $title = 'Enter Sandman'): array
    {
        $track = $this->makeTrackDto(
            title: $title,
            album: 'Metallica',
            artist: 'Metallica',
            windowsPath: 'F:\\Music\\Metallica\\Metallica\\01. Enter Sandman.mp3',
            albumPath: 'F:\\Music\\Metallica\\Metallica',
            number: 1,
        );

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
                'artists' => ['Metallica'],
                'tracks' => [$track],
            ]],
        ];
    }

    private function makeTrackDto(
        string $title,
        string $album,
        string $artist,
        string $windowsPath,
        string $albumPath,
        int $number,
    ): ParsedTrackDto {
        return ParsedTrackDto::from([
            'linux_path' => '/tmp/' . basename(str_replace('\\', '/', $windowsPath)),
            'windows_path' => $windowsPath,
            'title' => $title,
            'album' => $album,
            'artist' => $artist,
            'genre' => null,
            'year' => '1991',
            'date' => '1991-08-12',
            'track_number' => $number,
            'disc_number' => 1,
            'duration' => '00:05:31',
            'bitrate' => 320,
            'album_cover_linux_path' => null,
            'album_windows_path' => $albumPath,
            'album_version' => null,
            'original_album' => null,
            'album_type_id' => 1,
        ]);
    }
}

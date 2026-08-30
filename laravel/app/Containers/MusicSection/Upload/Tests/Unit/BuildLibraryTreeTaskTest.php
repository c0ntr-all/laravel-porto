<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\MusicSection\Album\Models\AlbumType;
use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Containers\MusicSection\Upload\Tasks\BuildLibraryTreeTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildLibraryTreeTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_collects_multiple_artists_and_marks_album_as_split(): void
    {
        $splitTypeId = (int) AlbumType::query()->where('slug', 'split')->value('id');

        $tracks = [
            ParsedTrackDto::from([
                'linux_path' => '/tmp/01.mp3',
                'windows_path' => 'F:\\Music\\Split\\01.mp3',
                'title' => 'Track A',
                'album' => 'Split Album',
                'artist' => 'Napalm Death',
                'year' => '1997',
                'date' => '1997-01-01',
                'album_windows_path' => 'F:\\Music\\Split',
            ]),
            ParsedTrackDto::from([
                'linux_path' => '/tmp/02.mp3',
                'windows_path' => 'F:\\Music\\Split\\02.mp3',
                'title' => 'Track B',
                'album' => 'Split Album',
                'artist' => 'Coalesce',
                'year' => '1997',
                'date' => '1997-01-01',
                'album_windows_path' => 'F:\\Music\\Split',
            ]),
        ];

        $tree = app(BuildLibraryTreeTask::class)->run(
            $tracks,
            'In Tongues We Speak',
            'F:\\Music\\In Tongues We Speak',
        );

        $this->assertCount(1, $tree['albums']);
        $this->assertEqualsCanonicalizing(['Napalm Death', 'Coalesce'], $tree['albums'][0]['artists']);
        $this->assertSame($splitTypeId, $tree['albums'][0]['album_type_id']);
        $this->assertSame('In Tongues We Speak', $tree['name']);
    }

    public function test_it_extracts_original_album_and_edition_from_remaster_title(): void
    {
        $tracks = [
            ParsedTrackDto::from([
                'linux_path' => '/tmp/01.mp3',
                'windows_path' => 'F:\\Music\\Metallica\\Master Of Puppets (Remastered)\\01.mp3',
                'title' => 'Battery',
                'album' => 'Master Of Puppets (Remastered)',
                'artist' => 'Metallica',
                'year' => '2017',
                'date' => '2017-01-01',
                'album_windows_path' => 'F:\\Music\\Metallica\\Master Of Puppets (Remastered)',
            ]),
        ];

        $tree = app(BuildLibraryTreeTask::class)->run(
            $tracks,
            'Metallica',
            'F:\\Music\\Metallica',
        );

        $this->assertSame('Master Of Puppets', $tree['albums'][0]['original_album']);
        $this->assertSame('Remastered', $tree['albums'][0]['attributes']);
    }

    public function test_it_extracts_rerecorded_edition(): void
    {
        $tracks = [
            ParsedTrackDto::from([
                'linux_path' => '/tmp/01.mp3',
                'windows_path' => 'F:\\Music\\Metallica\\Master Of Puppets (Rerecorded)\\01.mp3',
                'title' => 'Battery',
                'album' => 'Master Of Puppets (Rerecorded)',
                'artist' => 'Metallica',
                'year' => '2026',
                'date' => '2026-01-01',
                'album_windows_path' => 'F:\\Music\\Metallica\\Master Of Puppets (Rerecorded)',
            ]),
        ];

        $tree = app(BuildLibraryTreeTask::class)->run(
            $tracks,
            'Metallica',
            'F:\\Music\\Metallica',
        );

        $this->assertSame('Master Of Puppets', $tree['albums'][0]['original_album']);
        $this->assertSame('Rerecorded', $tree['albums'][0]['attributes']);
    }
}

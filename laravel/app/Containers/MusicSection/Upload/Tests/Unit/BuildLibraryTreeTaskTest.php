<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tests\Unit;

use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Containers\MusicSection\Upload\Tasks\BuildLibraryTreeTask;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BuildLibraryTreeTaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('album_types', collect([
            (object) ['id' => 1, 'slug' => 'studio', 'name' => 'studio'],
            (object) ['id' => 2, 'slug' => 'ep', 'name' => 'ep'],
            (object) ['id' => 3, 'slug' => 'single', 'name' => 'single'],
            (object) ['id' => 4, 'slug' => 'maxi-single', 'name' => 'maxi-single'],
            (object) ['id' => 5, 'slug' => 'split', 'name' => 'split'],
            (object) ['id' => 6, 'slug' => 'live', 'name' => 'live'],
        ]));
    }

    public function test_it_collects_multiple_artists_and_marks_album_as_split(): void
    {
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
        $this->assertSame(5, $tree['albums'][0]['album_type_id']);
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

        $this->assertSame('Master Of Puppets', $tree['albums'][0]['name']);
        $this->assertSame('Master Of Puppets', $tree['albums'][0]['original_album']);
        $this->assertSame('Remastered', $tree['albums'][0]['edition']);
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

        $this->assertSame('Master Of Puppets', $tree['albums'][0]['name']);
        $this->assertSame('Master Of Puppets', $tree['albums'][0]['original_album']);
        $this->assertSame('Rerecorded', $tree['albums'][0]['edition']);
        $this->assertSame('Rerecorded', $tree['albums'][0]['attributes']);
    }

    public function test_it_extracts_a_custom_edition_without_creating_a_new_album_type(): void
    {
        $tracks = [
            ParsedTrackDto::from([
                'linux_path' => '/tmp/01.mp3',
                'windows_path' => 'F:\\Music\\Metallica\\Master Of Puppets (Limited Digipack Edition)\\01.mp3',
                'title' => 'Battery',
                'album' => 'Master Of Puppets (Limited Digipack Edition)',
                'artist' => 'Metallica',
                'year' => '2017',
                'date' => '2017-01-01',
                'album_windows_path' => 'F:\\Music\\Metallica\\Master Of Puppets (Limited Digipack Edition)',
            ]),
        ];

        $tree = app(BuildLibraryTreeTask::class)->run(
            $tracks,
            'Metallica',
            'F:\\Music\\Metallica',
        );

        $this->assertSame('Master Of Puppets', $tree['albums'][0]['name']);
        $this->assertSame('Limited Digipack Edition', $tree['albums'][0]['edition']);
        $this->assertSame(1, $tree['albums'][0]['album_type_id']);
    }

    public function test_it_maps_live_parentheses_to_album_type_not_edition(): void
    {
        $tracks = [
            ParsedTrackDto::from([
                'linux_path' => '/tmp/01.mp3',
                'windows_path' => 'F:\\Music\\Iron Maiden\\Killers (Live)\\01.mp3',
                'title' => 'Wrathchild',
                'album' => 'Killers (Live)',
                'artist' => 'Iron Maiden',
                'year' => '1993',
                'date' => '1993-01-01',
                'album_windows_path' => 'F:\\Music\\Iron Maiden\\Killers (Live)',
            ]),
        ];

        $tree = app(BuildLibraryTreeTask::class)->run(
            $tracks,
            'Iron Maiden',
            'F:\\Music\\Iron Maiden',
        );

        $this->assertSame('Killers', $tree['albums'][0]['name']);
        $this->assertSame(6, $tree['albums'][0]['album_type_id']);
        $this->assertNull($tree['albums'][0]['edition']);
        $this->assertNull($tree['albums'][0]['original_album']);
    }
}

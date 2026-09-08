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

    public function test_it_merges_cd_folders_into_one_album_and_strips_credits_from_titles(): void
    {
        $tracks = [
            ParsedTrackDto::from([
                'linux_path' => '/tmp/cd1/01.mp3',
                'windows_path' => 'F:\\Music\\GNR\\Illusion (CD1)\\01.mp3',
                'title' => 'Right Next Door To Hell (feat. Axl)',
                'album' => 'Use Your Illusion (CD1)',
                'artist' => 'Guns N\' Roses',
                'year' => '1991',
                'date' => '1991-01-01',
                'album_windows_path' => 'F:\\Music\\GNR\\Illusion (CD1)',
                'disc_number' => 1,
            ]),
            ParsedTrackDto::from([
                'linux_path' => '/tmp/cd2/01.mp3',
                'windows_path' => 'F:\\Music\\GNR\\Illusion (CD2)\\01.mp3',
                'title' => 'Civil War [prod. Bob]',
                'album' => 'Use Your Illusion (CD2)',
                'artist' => 'Guns N\' Roses',
                'year' => '1991',
                'date' => '1991-01-01',
                'album_windows_path' => 'F:\\Music\\GNR\\Illusion (CD2)',
                'disc_number' => 2,
            ]),
        ];

        $tree = app(BuildLibraryTreeTask::class)->run(
            $tracks,
            'Guns N\' Roses',
            'F:\\Music\\GNR',
        );

        $this->assertCount(1, $tree['albums']);
        $album = $tree['albums'][0];
        $this->assertSame('Use Your Illusion', $album['name']);
        $this->assertCount(2, $album['tracks']);
        $this->assertSame(
            [
                ['number' => 1, 'name' => 'CD 1', 'tracks_count' => 1],
                ['number' => 2, 'name' => 'CD 2', 'tracks_count' => 1],
            ],
            $album['discs'],
        );
        $this->assertSame('Right Next Door To Hell', $album['tracks'][0]->title);
        $this->assertSame('feat. Axl', $album['tracks'][0]->credits);
        $this->assertSame('Civil War', $album['tracks'][1]->title);
        $this->assertSame('prod. Bob', $album['tracks'][1]->credits);
        $this->assertSame(1, $album['tracks'][0]->disc_number);
        $this->assertSame(2, $album['tracks'][1]->disc_number);
    }

    public function test_it_extracts_featured_artists_from_title_and_artist_tag(): void
    {
        $tracks = [
            ParsedTrackDto::from([
                'linux_path' => '/tmp/01.mp3',
                'windows_path' => 'F:\\Music\\Drake\\Nothing Was The Same\\01.mp3',
                'title' => 'Hold On We\'re Going Home (feat. Majid Jordan)',
                'album' => 'Nothing Was The Same',
                'artist' => 'Drake feat. Rihanna',
                'year' => '2013',
                'date' => '2013-01-01',
                'album_windows_path' => 'F:\\Music\\Drake\\Nothing Was The Same',
            ]),
        ];

        $tree = app(BuildLibraryTreeTask::class)->run(
            $tracks,
            'Drake',
            'F:\\Music\\Drake',
        );

        $track = $tree['albums'][0]['tracks'][0];
        $this->assertSame('Hold On We\'re Going Home', $track->title);
        $this->assertSame('Drake', $track->artist);
        $this->assertEqualsCanonicalizing(['Majid Jordan', 'Rihanna'], $track->featured_artists);
        $this->assertSame(['Drake'], $tree['albums'][0]['artists']);
    }
}

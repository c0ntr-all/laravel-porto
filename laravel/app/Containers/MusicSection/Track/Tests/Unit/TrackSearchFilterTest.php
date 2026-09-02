<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Track\Data\Filters\TrackSearchFilter;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_plain_text_also_searches_track_credits(): void
    {
        [$user, $album] = $this->makeCatalog();
        $artist = $this->makeArtist($user, 'Kendrick Lamar', 'F:\\Music\\Kendrick');
        $track = $this->makeTrack($album, 'HUMBLE.', $artist, 'feat. Jay Rock');
        $this->makeTrack($album, 'DNA.', $artist);

        $ids = $this->searchIds('Jay Rock');

        $this->assertEquals([$track->id], $ids);
    }

    public function test_plain_text_searches_track_names_across_artists(): void
    {
        [$metallicaOne, $u2One] = $this->seedOneTracks();

        $ids = $this->searchIds('One');

        $this->assertEqualsCanonicalizing([$metallicaOne->id, $u2One->id], $ids);
    }

    public function test_hyphen_limits_search_to_tracks_of_the_artist(): void
    {
        [$metallicaOne, $u2One] = $this->seedOneTracks();

        $ids = $this->searchIds('Metallica - One');

        $this->assertEquals([$metallicaOne->id], $ids);
        $this->assertNotContains($u2One->id, $ids);
    }

    public function test_hyphen_uses_everything_after_the_first_dash_as_track_name(): void
    {
        [$user, $album] = $this->makeCatalog();
        $artist = $this->makeArtist($user, 'AC/DC', 'F:\\Music\\ACDC');
        $track = $this->makeTrack($album, 'Back In Black - Live', $artist);

        $ids = $this->searchIds('AC/DC - Back In Black - Live');

        $this->assertEquals([$track->id], $ids);
    }

    public function test_search_shorter_than_three_characters_is_ignored(): void
    {
        [$metallicaOne] = $this->seedOneTracks();

        $ids = $this->searchIds('On');

        $this->assertContains($metallicaOne->id, $ids);
        $this->assertCount(2, $ids);
    }

    public function test_artist_only_hyphen_query_returns_all_tracks_of_that_artist(): void
    {
        [$user, $album] = $this->makeCatalog();
        $metallica = $this->makeArtist($user, 'Metallica', 'F:\\Music\\Metallica');
        $megadeth = $this->makeArtist($user, 'Megadeth', 'F:\\Music\\Megadeth');
        $enter = $this->makeTrack($album, 'Enter Sandman', $metallica);
        $this->makeTrack($album, 'Holy Wars', $megadeth);

        $ids = $this->searchIds('Metallica -');

        $this->assertEquals([$enter->id], $ids);
    }

    /**
     * @return array{0: Track, 1: Track}
     */
    private function seedOneTracks(): array
    {
        [$user, $album] = $this->makeCatalog();
        $metallica = $this->makeArtist($user, 'Metallica', 'F:\\Music\\Metallica');
        $u2 = $this->makeArtist($user, 'U2', 'F:\\Music\\U2');

        return [
            $this->makeTrack($album, 'One', $metallica),
            $this->makeTrack($album, 'One', $u2),
        ];
    }

    /**
     * @return list<int>
     */
    private function searchIds(string $term): array
    {
        $query = Track::query();
        (new TrackSearchFilter())($query, $term, 'search');

        return $query->pluck('id')->map(static fn (mixed $id) => (int) $id)->all();
    }

    /**
     * @return array{0: User, 1: Album}
     */
    private function makeCatalog(): array
    {
        $user = User::factory()->create();
        $album = Album::create([
            'name' => 'Shared Album',
            'path' => 'F:\\Music\\Shared',
            'album_type_id' => 1,
        ]);

        return [$user, $album];
    }

    private function makeArtist(User $user, string $name, string $path): Artist
    {
        return Artist::create([
            'user_id' => $user->id,
            'name' => $name,
            'path' => $path,
        ]);
    }

    private function makeTrack(Album $album, string $name, Artist $artist, ?string $credits = null): Track
    {
        $track = Track::create([
            'album_id' => $album->id,
            'name' => $name,
            'credits' => $credits,
            'number' => 1,
        ]);
        $track->artists()->attach($artist->id);

        return $track;
    }
}

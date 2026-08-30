<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\Tasks\AssertAlbumCanBeGroupedUnderTask;
use App\Containers\MusicSection\Album\UI\Actions\CreateAlbumAction;
use App\Containers\MusicSection\Album\UI\Actions\UpdateAlbumAction;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\UI\Actions\ListAlbumsByArtistAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AlbumGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_album_can_be_grouped_as_a_version_of_the_main_release(): void
    {
        [$user, $artist, $original] = $this->seedArtistWithAlbum('Master Of Puppets');
        $this->actingAs($user, 'api');

        $remaster = app(CreateAlbumAction::class)->handle([
            'name' => 'Master Of Puppets',
            'edition' => 'Remastered',
            'parent_id' => $original->id,
            'artist_ids' => [$artist->id],
            'path' => 'F:\\Music\\Metallica\\Master Of Puppets Remastered',
        ]);

        $this->assertSame($original->id, $remaster->parent_id);
        $this->assertSame('Remastered', $remaster->edition);
        $this->assertTrue($original->fresh()->versions->contains('id', $remaster->id));
    }

    public function test_artist_album_list_returns_only_main_albums_with_nested_versions(): void
    {
        [$user, $artist, $original] = $this->seedArtistWithAlbum('Master Of Puppets');
        $this->actingAs($user, 'api');

        $remaster = app(CreateAlbumAction::class)->handle([
            'name' => 'Master Of Puppets',
            'edition' => 'Remastered',
            'parent_id' => $original->id,
            'artist_ids' => [$artist->id],
            'path' => 'F:\\Music\\Metallica\\Master Of Puppets Remastered',
        ]);
        app(CreateAlbumAction::class)->handle([
            'name' => 'Master Of Puppets',
            'edition' => 'Rerecorded',
            'parent_id' => $original->id,
            'artist_ids' => [$artist->id],
            'path' => 'F:\\Music\\Metallica\\Master Of Puppets Rerecorded',
        ]);

        $albums = app(ListAlbumsByArtistAction::class)->handle($artist);

        $this->assertCount(1, $albums);
        $this->assertSame($original->id, $albums->first()->id);
        $this->assertEqualsCanonicalizing(
            ['Remastered', 'Rerecorded'],
            $albums->first()->versions->pluck('edition')->all(),
        );
        $this->assertTrue($albums->first()->versions->contains('id', $remaster->id));
    }

    public function test_album_cannot_be_a_version_of_itself(): void
    {
        [, $artist, $album] = $this->seedArtistWithAlbum('Master Of Puppets');

        $this->expectException(ValidationException::class);

        app(AssertAlbumCanBeGroupedUnderTask::class)->run($album->id, [$artist->id], $album);
    }

    public function test_version_cannot_be_used_as_a_parent(): void
    {
        [$user, $artist, $original] = $this->seedArtistWithAlbum('Master Of Puppets');
        $this->actingAs($user, 'api');

        $remaster = app(CreateAlbumAction::class)->handle([
            'name' => 'Master Of Puppets',
            'edition' => 'Remastered',
            'parent_id' => $original->id,
            'artist_ids' => [$artist->id],
            'path' => 'F:\\Music\\Metallica\\Master Of Puppets Remastered',
        ]);

        $this->expectException(ValidationException::class);

        app(CreateAlbumAction::class)->handle([
            'name' => 'Master Of Puppets',
            'edition' => 'Deluxe',
            'parent_id' => $remaster->id,
            'artist_ids' => [$artist->id],
            'path' => 'F:\\Music\\Metallica\\Master Of Puppets Deluxe',
        ]);
    }

    public function test_album_with_versions_cannot_become_a_version(): void
    {
        [$user, $artist, $original] = $this->seedArtistWithAlbum('Master Of Puppets');
        $other = $this->makeAlbum($artist, 'Ride The Lightning', 'F:\\Music\\Metallica\\Ride The Lightning');
        $this->actingAs($user, 'api');

        app(CreateAlbumAction::class)->handle([
            'name' => 'Master Of Puppets',
            'edition' => 'Remastered',
            'parent_id' => $original->id,
            'artist_ids' => [$artist->id],
            'path' => 'F:\\Music\\Metallica\\Master Of Puppets Remastered',
        ]);

        $this->expectException(ValidationException::class);

        app(UpdateAlbumAction::class)->handle($original, [
            'parent_id' => $other->id,
        ]);
    }

    public function test_version_must_share_an_artist_with_the_main_album(): void
    {
        [$user, $artist, $original] = $this->seedArtistWithAlbum('Master Of Puppets');
        $otherArtist = Artist::query()->create([
            'user_id' => $user->id,
            'name' => 'Megadeth',
            'path' => 'F:\\Music\\Megadeth',
        ]);

        $this->actingAs($user, 'api');

        $this->expectException(ValidationException::class);

        app(CreateAlbumAction::class)->handle([
            'name' => 'Master Of Puppets',
            'edition' => 'Remastered',
            'parent_id' => $original->id,
            'artist_ids' => [$otherArtist->id],
            'path' => 'F:\\Music\\Megadeth\\Master Of Puppets Remastered',
        ]);
    }

    public function test_version_can_be_detached_from_the_main_album(): void
    {
        [$user, $artist, $original] = $this->seedArtistWithAlbum('Master Of Puppets');
        $this->actingAs($user, 'api');

        $remaster = app(CreateAlbumAction::class)->handle([
            'name' => 'Master Of Puppets',
            'edition' => 'Remastered',
            'parent_id' => $original->id,
            'artist_ids' => [$artist->id],
            'path' => 'F:\\Music\\Metallica\\Master Of Puppets Remastered',
        ]);

        $updated = app(UpdateAlbumAction::class)->handle($remaster, [
            'parent_id' => null,
        ]);

        $this->assertNull($updated->parent_id);
        $this->assertFalse($original->fresh()->versions->contains('id', $remaster->id));
    }

    /**
     * @return array{0: User, 1: Artist, 2: Album}
     */
    private function seedArtistWithAlbum(string $albumName): array
    {
        $user = User::factory()->create();
        $artist = Artist::query()->create([
            'user_id' => $user->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);
        $album = $this->makeAlbum($artist, $albumName, 'F:\\Music\\Metallica\\' . $albumName);

        return [$user, $artist, $album];
    }

    private function makeAlbum(Artist $artist, string $name, string $path): Album
    {
        $album = Album::query()->create([
            'name' => $name,
            'path' => $path,
            'album_type_id' => 1,
        ]);
        $album->artists()->attach($artist->id);

        return $album;
    }
}

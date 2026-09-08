<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Track\Enums\TrackArtistRoleEnum;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTracksByArtistTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_track_list_uses_track_links_not_the_whole_split_album(): void
    {
        $user = User::factory()->create();
        $napalm = $this->makeArtist($user, 'Napalm Death', 'F:\\Music\\Napalm Death');
        $coalesce = $this->makeArtist($user, 'Coalesce', 'F:\\Music\\Coalesce');
        $album = $this->makeAlbum('In Tongues We Speak', 'F:\\Music\\Split');
        $album->artists()->attach([$napalm->id, $coalesce->id]);

        $napalmTrack = $this->makeTrack($album, 'Food Chain', 1);
        $coalesceTrack = $this->makeTrack($album, 'A New Language', 2);
        $napalmTrack->artists()->sync([
            $napalm->id => ['is_author' => true, 'role' => TrackArtistRoleEnum::Primary->value],
        ]);
        $coalesceTrack->artists()->sync([
            $coalesce->id => ['is_author' => true, 'role' => TrackArtistRoleEnum::Primary->value],
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists/'.$napalm->id.'/tracks');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(static fn (mixed $id) => (int) $id)->all();
        $this->assertEquals([$napalmTrack->id], $ids);
        $this->assertNotContains($coalesceTrack->id, $ids);
    }

    public function test_featured_tracks_are_listed_and_can_be_filtered_by_role(): void
    {
        $user = User::factory()->create();
        $drake = $this->makeArtist($user, 'Drake', 'F:\\Music\\Drake');
        $rihanna = $this->makeArtist($user, 'Rihanna', 'F:\\Music\\Rihanna');
        $album = $this->makeAlbum('Nothing Was The Same', 'F:\\Music\\Drake\\NWTS');
        $album->artists()->attach($drake->id);

        $own = $this->makeTrack($album, 'Started From The Bottom', 1);
        $feat = $this->makeTrack($album, 'Take Care', 2);
        $own->artists()->sync([
            $drake->id => ['is_author' => true, 'role' => TrackArtistRoleEnum::Primary->value],
        ]);
        $feat->artists()->sync([
            $drake->id => ['is_author' => true, 'role' => TrackArtistRoleEnum::Primary->value],
            $rihanna->id => ['is_author' => false, 'role' => TrackArtistRoleEnum::Featured->value],
        ]);

        $all = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists/'.$rihanna->id.'/tracks');
        $all->assertOk();
        $this->assertEquals([$feat->id], collect($all->json('data'))->pluck('id')->map(static fn (mixed $id) => (int) $id)->all());

        $featured = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists/'.$rihanna->id.'/tracks?filter[role]=featured');
        $featured->assertOk();
        $this->assertEquals([$feat->id], collect($featured->json('data'))->pluck('id')->map(static fn (mixed $id) => (int) $id)->all());

        $primary = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists/'.$rihanna->id.'/tracks?filter[role]=primary');
        $primary->assertOk();
        $this->assertSame([], $primary->json('data'));
    }

    private function makeArtist(User $user, string $name, string $path): Artist
    {
        return Artist::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'path' => $path,
        ]);
    }

    private function makeAlbum(string $name, string $path): Album
    {
        return Album::query()->create([
            'name' => $name,
            'path' => $path,
            'album_type_id' => 1,
        ]);
    }

    private function makeTrack(Album $album, string $name, int $number): Track
    {
        return Track::query()->create([
            'album_id' => $album->id,
            'name' => $name,
            'number' => $number,
        ]);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAlbumsByArtistTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_artist_albums(): void
    {
        $user = User::factory()->create();
        $artist = $this->makeArtist($user);

        $this->getJson('/api/v1/music/artists/'.$artist->id.'/albums')->assertUnauthorized();
    }

    public function test_default_order_is_date_descending(): void
    {
        $user = User::factory()->create();
        $artist = $this->makeArtist($user);

        $oldest = $this->makeAlbum($artist, 'Kill Em All', 'F:\\Music\\Metallica\\Kill Em All', [
            'date' => '1983-07-25',
            'album_type_id' => 1,
        ]);
        $newest = $this->makeAlbum($artist, '72 Seasons', 'F:\\Music\\Metallica\\72 Seasons', [
            'date' => '2023-04-14',
            'album_type_id' => 1,
        ]);
        $middle = $this->makeAlbum($artist, 'Master Of Puppets', 'F:\\Music\\Metallica\\Master Of Puppets', [
            'date' => '1986-03-03',
            'album_type_id' => 1,
        ]);
        $single = $this->makeAlbum($artist, 'Until It Sleeps', 'F:\\Music\\Metallica\\Until It Sleeps', [
            'date' => '1996-05-21',
            'album_type_id' => 3,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists/'.$artist->id.'/albums');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(static fn (mixed $id) => (int) $id)->all();
        $this->assertSame(
            [(int) $newest->id, (int) $single->id, (int) $middle->id, (int) $oldest->id],
            $ids,
        );
        $this->assertSame(
            ['2023-04-14', '1996-05-21', '1986-03-03', '1983-07-25'],
            collect($response->json('data'))->pluck('attributes.date')->all(),
        );
    }

    public function test_filter_by_album_type_id_returns_only_studio_albums(): void
    {
        $user = User::factory()->create();
        $artist = $this->makeArtist($user);

        $studioA = $this->makeAlbum($artist, 'Ride The Lightning', 'F:\\Music\\Metallica\\RTL', [
            'date' => '1984-07-27',
            'album_type_id' => 1,
        ]);
        $studioB = $this->makeAlbum($artist, 'Load', 'F:\\Music\\Metallica\\Load', [
            'date' => '1996-06-04',
            'album_type_id' => 1,
        ]);
        $this->makeAlbum($artist, 'Some Kind Of Monster', 'F:\\Music\\Metallica\\SKOM', [
            'date' => '2004-07-13',
            'album_type_id' => 2,
        ]);
        $this->makeAlbum($artist, 'Until It Sleeps', 'F:\\Music\\Metallica\\UIS', [
            'date' => '1996-05-21',
            'album_type_id' => 3,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/artists/'.$artist->id.'/albums?filter[album_type_id]=1');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(static fn (mixed $id) => (int) $id)->all();
        $this->assertSame([(int) $studioB->id, (int) $studioA->id], $ids);
        $typeIds = collect($response->json('data'))
            ->pluck('attributes.album_type_id')
            ->map(static fn (mixed $id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $this->assertSame([1], $typeIds);
    }

    private function makeArtist(User $user, string $name = 'Metallica', string $path = 'F:\\Music\\Metallica'): Artist
    {
        return Artist::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'path' => $path,
        ]);
    }

    private function makeAlbum(Artist $artist, string $name, string $path, array $extra = []): Album
    {
        $album = Album::query()->create(array_merge([
            'name' => $name,
            'path' => $path,
            'album_type_id' => 1,
        ], $extra));
        $album->artists()->attach($artist->id);

        return $album;
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAlbumsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_albums(): void
    {
        $this->getJson('/api/v1/music/albums')->assertUnauthorized();
    }

    public function test_filter_by_name_is_case_insensitive(): void
    {
        $user = User::factory()->create();
        $artist = $this->makeArtist($user);
        $puppets = $this->makeAlbum($artist, 'Master Of Puppets', 'F:\\Music\\Metallica\\Master Of Puppets');
        $this->makeAlbum($artist, 'Ride The Lightning', 'F:\\Music\\Metallica\\Ride The Lightning');

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/albums?filter[name]=puppets');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($puppets->id, (int) $response->json('data.0.id'));
    }

    public function test_filter_by_name_matches_edition_and_returns_the_root_album(): void
    {
        $user = User::factory()->create();
        $artist = $this->makeArtist($user);
        $original = $this->makeAlbum($artist, 'Master Of Puppets', 'F:\\Music\\Metallica\\Master Of Puppets');
        $this->makeAlbum(
            $artist,
            'Master Of Puppets',
            'F:\\Music\\Metallica\\Master Of Puppets Digipack',
            [
                'parent_id' => $original->id,
                'edition' => 'Limited Digipack Edition',
            ],
        );
        $this->makeAlbum($artist, 'Killers', 'F:\\Music\\Maiden\\Killers');

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/albums?filter[name]=digipack');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($original->id, (int) $response->json('data.0.id'));
    }

    public function test_filter_has_multiple_discs_returns_only_multi_disc_releases(): void
    {
        $user = User::factory()->create();
        $artist = $this->makeArtist($user);
        $multi = $this->makeAlbum($artist, 'Use Your Illusion', 'F:\\Music\\GNR\\Illusion');
        $single = $this->makeAlbum($artist, 'Appetite For Destruction', 'F:\\Music\\GNR\\Appetite');

        $multi->discs()->create(['number' => 1, 'name' => 'CD 1']);
        $multi->discs()->create(['number' => 2, 'name' => 'CD 2']);
        $single->discs()->create(['number' => 1, 'name' => 'CD 1']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/albums?filter[has_multiple_discs]=1');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($multi->id, (int) $response->json('data.0.id'));
        $this->assertSame(2, (int) $response->json('data.0.attributes.discs_count'));
    }

    private function makeArtist(User $user): Artist
    {
        return Artist::query()->create([
            'user_id' => $user->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
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

<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTracksTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_tracks(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'name' => 'Test Album',
            'path' => '/music/test-album',
            'album_type_id' => 1,
        ]);

        Track::create([
            'album_id' => $album->id,
            'name' => 'Track One',
            'number' => 1,
        ]);
        Track::create([
            'album_id' => $album->id,
            'name' => 'Track Two',
            'number' => 2,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'attributes' => ['name', 'image', 'duration', 'rate']],
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_list_tracks(): void
    {
        $response = $this->getJson('/api/v1/music/tracks');

        $response->assertUnauthorized();
    }

    public function test_tracks_are_ordered_by_created_at_desc(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'name' => 'Test Album',
            'path' => '/music/test-album',
            'album_type_id' => 1,
        ]);

        $first = Track::create([
            'album_id' => $album->id,
            'name' => 'First Track',
            'number' => 1,
        ]);
        $second = Track::create([
            'album_id' => $album->id,
            'name' => 'Second Track',
            'number' => 2,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals($second->id, $data[0]['id']);
        $this->assertEquals($first->id, $data[1]['id']);
    }
}

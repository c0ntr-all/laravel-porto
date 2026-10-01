<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\History\Models\History;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_history(): void
    {
        $this->getJson('/api/v1/music/history')->assertUnauthorized();
    }

    public function test_user_can_list_history_with_nested_track_includes(): void
    {
        $user = User::factory()->create();
        $track = $this->makeTrack($user, 'Enter Sandman');

        History::query()->create([
            'user_id' => $user->id,
            'track_id' => $track->id,
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/history?include=track,track.artists,track.album')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'history')
            ->assertJsonPath('data.0.relationships.track.data.type', 'tracks')
            ->assertJsonPath('data.0.relationships.track.data.id', (string) $track->id)
            ->assertJsonFragment([
                'type' => 'tracks',
                'id' => (string) $track->id,
            ]);
    }

    public function test_user_history_includes_soft_deleted_tracks(): void
    {
        $user = User::factory()->create();
        $track = $this->makeTrack($user, 'Deleted Track');

        History::query()->create([
            'user_id' => $user->id,
            'track_id' => $track->id,
        ]);

        $track->delete();

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/history?include=track')
            ->assertOk()
            ->assertJsonPath('data.0.relationships.track.data.id', (string) $track->id)
            ->assertJsonFragment([
                'type' => 'tracks',
                'id' => (string) $track->id,
            ]);
    }

    private function makeTrack(User $user, string $name): Track
    {
        $artist = Artist::query()->create([
            'user_id' => $user->id,
            'name' => 'Metallica',
            'path' => 'F:\\Music\\Metallica',
        ]);

        $album = Album::query()->create([
            'name' => 'Black Album',
            'path' => 'F:\\Music\\Metallica\\Black Album',
            'album_type_id' => 1,
        ]);
        $album->artists()->attach($artist->id);

        $track = Track::query()->create([
            'album_id' => $album->id,
            'name' => $name,
            'number' => 1,
            'path' => 'F:\\Music\\Metallica\\Black Album\\01. ' . $name . '.mp3',
            'duration' => '00:05:31',
        ]);
        $track->artists()->attach($artist->id, [
            'is_author' => true,
            'role' => 'primary',
        ]);

        return $track;
    }
}

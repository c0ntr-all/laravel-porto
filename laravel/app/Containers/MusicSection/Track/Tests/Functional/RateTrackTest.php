<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Track\Models\Rate;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateTrackTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_rate_track_and_change_rating(): void
    {
        $user = User::factory()->create();
        $track = $this->makeTrack();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/tracks/'.$track->id.'/rate', ['rate' => 2])
            ->assertOk();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/tracks/'.$track->id.'/rate', ['rate' => 4])
            ->assertOk();

        $this->assertDatabaseHas('music_track_rates', [
            'user_id' => $user->id,
            'track_id' => $track->id,
            'rate' => 4,
        ]);
        $this->assertSame(1, Rate::withoutGlobalScopes()->where('track_id', $track->id)->count());
    }

    public function test_user_can_reset_track_rating(): void
    {
        $user = User::factory()->create();
        $track = $this->makeTrack();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/tracks/'.$track->id.'/rate', ['rate' => 3])
            ->assertOk();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/tracks/'.$track->id.'/rate', ['rate' => 0])
            ->assertOk();

        $this->assertDatabaseMissing('music_track_rates', [
            'user_id' => $user->id,
            'track_id' => $track->id,
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks/'.$track->id)
            ->assertOk()
            ->assertJsonPath('data.attributes.rate', 0);
    }

    public function test_reset_does_not_remove_another_users_rating(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $track = $this->makeTrack();

        $this->actingAs($owner, 'api')
            ->postJson('/api/v1/music/tracks/'.$track->id.'/rate', ['rate' => 4])
            ->assertOk();

        $this->actingAs($other, 'api')
            ->postJson('/api/v1/music/tracks/'.$track->id.'/rate', ['rate' => 2])
            ->assertOk();

        $this->actingAs($other, 'api')
            ->postJson('/api/v1/music/tracks/'.$track->id.'/rate', ['rate' => 0])
            ->assertOk();

        $this->assertDatabaseHas('music_track_rates', [
            'user_id' => $owner->id,
            'track_id' => $track->id,
            'rate' => 4,
        ]);
        $this->assertDatabaseMissing('music_track_rates', [
            'user_id' => $other->id,
            'track_id' => $track->id,
        ]);
    }

    private function makeTrack(): Track
    {
        $album = Album::query()->create([
            'name' => 'Black Album',
            'path' => '/music/black-album',
            'album_type_id' => 1,
        ]);

        return Track::query()->create([
            'album_id' => $album->id,
            'name' => 'Enter Sandman',
            'number' => 1,
        ]);
    }
}

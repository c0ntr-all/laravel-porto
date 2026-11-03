<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_listened_progress(): void
    {
        $user = User::factory()->create();
        $track = $this->makeTrack();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/history', [
                'track_id' => $track->id,
                'progress' => 65.43,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.progress', 65.43)
            ->assertJsonPath('data.attributes.track_id', $track->id);

        $this->assertDatabaseHas('music_history', [
            'user_id' => $user->id,
            'track_id' => $track->id,
            'progress' => 65.43,
        ]);
    }

    public function test_progress_accepts_comma_decimal_separator(): void
    {
        $user = User::factory()->create();
        $track = $this->makeTrack();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/history', [
                'track_id' => $track->id,
                'progress' => '65,43',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.progress', 65.43);
    }

    public function test_progress_rejects_more_than_two_decimal_places(): void
    {
        $user = User::factory()->create();
        $track = $this->makeTrack();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/history', [
                'track_id' => $track->id,
                'progress' => 65.432,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['progress']);
    }

    public function test_progress_rejects_values_above_100(): void
    {
        $user = User::factory()->create();
        $track = $this->makeTrack();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/music/history', [
                'track_id' => $track->id,
                'progress' => 100.01,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['progress']);
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

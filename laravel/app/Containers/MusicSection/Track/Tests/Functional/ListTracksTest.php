<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
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

    public function test_search_by_plain_text_finds_tracks_by_name(): void
    {
        $user = User::factory()->create();
        $this->createTrackWithArtist($user, 'Metallica', 'Enter Sandman');
        $this->createTrackWithArtist($user, 'Megadeth', 'Holy Wars');

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks?' . http_build_query([
                'filter' => ['search' => 'Enter'],
            ]));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('attributes.name')->all();
        $this->assertEquals(['Enter Sandman'], $names);
    }

    public function test_search_with_hyphen_finds_track_within_artist(): void
    {
        $user = User::factory()->create();
        $this->createTrackWithArtist($user, 'Metallica', 'One');
        $this->createTrackWithArtist($user, 'U2', 'One');

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks?' . http_build_query([
                'filter' => ['search' => 'Metallica - One'],
            ]));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('attributes.name')->all();
        $this->assertCount(1, $names);
        $this->assertEquals(['One'], $names);
    }

    public function test_search_shorter_than_three_characters_does_not_filter(): void
    {
        $user = User::factory()->create();
        $this->createTrackWithArtist($user, 'Metallica', 'One');
        $this->createTrackWithArtist($user, 'Megadeth', 'Holy Wars');

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks?' . http_build_query([
                'filter' => ['search' => 'On'],
            ]));

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    private function createTrackWithArtist(User $user, string $artistName, string $trackName): Track
    {
        $album = Album::create([
            'name' => $artistName . ' Album',
            'path' => '/music/' . $artistName,
            'album_type_id' => 1,
        ]);
        $artist = Artist::create([
            'user_id' => $user->id,
            'name' => $artistName,
            'path' => 'F:\\Music\\' . $artistName,
        ]);
        $track = Track::create([
            'album_id' => $album->id,
            'name' => $trackName,
            'number' => 1,
        ]);
        $track->artists()->attach($artist->id);

        return $track;
    }

    public function test_filter_tags_or_matches_any_selected_tag(): void
    {
        $user = User::factory()->create();
        [$metal, $dark] = $this->makeTags();
        $metalTrack = $this->createTrackWithArtist($user, 'Metallica', 'Enter Sandman');
        $darkTrack = $this->createTrackWithArtist($user, 'Type O Negative', 'Black No. 1');
        $this->createTrackWithArtist($user, 'Untagged', 'Plain');
        $metalTrack->tags()->attach($metal->id);
        $darkTrack->tags()->attach($dark->id);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks?'.http_build_query([
                'filter' => [
                    'tags' => $metal->id.','.$dark->id,
                    'tags_match' => 'or',
                ],
            ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->assertEqualsCanonicalizing([$metalTrack->id, $darkTrack->id], $ids);
    }

    public function test_filter_rate_returns_tracks_with_selected_ratings(): void
    {
        $user = User::factory()->create();
        $loved = $this->createTrackWithArtist($user, 'Metallica', 'One');
        $ok = $this->createTrackWithArtist($user, 'Megadeth', 'Trust');
        $this->createTrackWithArtist($user, 'Slayer', 'Raining Blood');
        $this->rateTrack($user, $loved, 4);
        $this->rateTrack($user, $ok, 2);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks?'.http_build_query([
                'filter' => ['rate' => '4,2'],
            ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->assertEqualsCanonicalizing([$loved->id, $ok->id], $ids);
    }

    public function test_sort_by_rate_desc_puts_highest_rated_first(): void
    {
        $user = User::factory()->create();
        $low = $this->createTrackWithArtist($user, 'A', 'Low');
        $high = $this->createTrackWithArtist($user, 'B', 'High');
        $this->rateTrack($user, $low, 1);
        $this->rateTrack($user, $high, 4);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks?sort=-rate');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->assertSame((int) $high->id, $ids[0]);
        $this->assertSame((int) $low->id, $ids[1]);
    }

    public function test_sort_by_rate_keeps_unrated_tracks_last(): void
    {
        $user = User::factory()->create();
        $unrated = $this->createTrackWithArtist($user, 'C', 'Unrated');
        $high = $this->createTrackWithArtist($user, 'B', 'High');
        $low = $this->createTrackWithArtist($user, 'A', 'Low');
        $this->rateTrack($user, $high, 4);
        $this->rateTrack($user, $low, 1);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks?sort=-rate');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->assertSame([(int) $high->id, (int) $low->id, (int) $unrated->id], $ids);
    }

    public function test_sort_by_rate_asc_keeps_unrated_tracks_last(): void
    {
        $user = User::factory()->create();
        $unrated = $this->createTrackWithArtist($user, 'C', 'Unrated');
        $high = $this->createTrackWithArtist($user, 'B', 'High');
        $low = $this->createTrackWithArtist($user, 'A', 'Low');
        $this->rateTrack($user, $high, 4);
        $this->rateTrack($user, $low, 1);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/music/tracks?sort=rate');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->assertSame([(int) $low->id, (int) $high->id, (int) $unrated->id], $ids);
    }

    /**
     * @return array{0: \App\Containers\MusicSection\Tag\Models\MusicTag, 1: \App\Containers\MusicSection\Tag\Models\MusicTag}
     */
    private function makeTags(): array
    {
        $group = \App\Containers\MusicSection\Tag\Models\MusicTagGroup::query()->create([
            'name' => 'Genre',
            'slug' => 'genre-'.uniqid(),
            'is_system' => true,
        ]);
        $metal = \App\Containers\MusicSection\Tag\Models\MusicTag::query()->create([
            'name' => 'Metal',
            'group_id' => $group->id,
            'is_active' => true,
        ]);
        $dark = \App\Containers\MusicSection\Tag\Models\MusicTag::query()->create([
            'name' => 'Dark',
            'group_id' => $group->id,
            'is_active' => true,
        ]);

        return [$metal, $dark];
    }

    private function rateTrack(User $user, Track $track, int $rate): void
    {
        \App\Containers\MusicSection\Track\Models\Rate::query()->create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'rate' => $rate,
        ]);
    }
}

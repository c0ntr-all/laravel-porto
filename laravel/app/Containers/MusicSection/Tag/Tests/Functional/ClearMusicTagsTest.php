<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Containers\MusicSection\Tag\Tasks\RecalculateAlbumAggregatedTagsTask;
use App\Containers\MusicSection\Tag\Tasks\RecalculateArtistAggregatedTagsTask;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClearMusicTagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_tag_payload_clears_track_album_and_artist(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'api'));

        $group = MusicTagGroup::query()->create([
            'name' => 'Genre',
            'is_system' => true,
        ]);
        $metal = MusicTag::query()->create([
            'name' => 'Metal',
            'group_id' => $group->id,
            'is_active' => true,
        ]);
        $artist = Artist::query()->create([
            'user_id' => $admin->id,
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
            'name' => 'Enter Sandman',
            'number' => 1,
        ]);
        $track->artists()->attach($artist->id);
        $track->tags()->attach($metal->id);
        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);
        app(RecalculateArtistAggregatedTagsTask::class)->run($artist->id);

        $this->actingAs($admin, 'api')
            ->post('/api/v1/music/tracks/'.$track->id, [
                '_method' => 'PATCH',
                'tags' => '',
            ])
            ->assertOk()
            ->assertJsonPath('data.relationships.tags.data', []);

        $this->assertDatabaseMissing('music_track_tag', ['track_id' => $track->id]);
        $this->assertDatabaseMissing('music_album_aggregated_tags', ['album_id' => $album->id]);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', ['artist_id' => $artist->id]);

        $track->tags()->attach($metal->id);
        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);
        app(RecalculateArtistAggregatedTagsTask::class)->run($artist->id);

        $this->actingAs($admin, 'api')
            ->post('/api/v1/music/albums/'.$album->id, [
                '_method' => 'PATCH',
                'tags' => '',
            ])
            ->assertOk()
            ->assertJsonPath('data.relationships.tags.data', []);

        $this->assertDatabaseMissing('music_track_tag', ['track_id' => $track->id]);
        $this->assertDatabaseMissing('music_album_aggregated_tags', ['album_id' => $album->id]);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', ['artist_id' => $artist->id]);

        $track->tags()->attach($metal->id);
        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);
        app(RecalculateArtistAggregatedTagsTask::class)->run($artist->id);

        $this->actingAs($admin, 'api')
            ->post('/api/v1/music/artists/'.$artist->id, [
                '_method' => 'PATCH',
                'tags' => '',
            ])
            ->assertOk()
            ->assertJsonPath('data.relationships.tags.data', []);

        $this->assertDatabaseMissing('music_track_tag', ['track_id' => $track->id]);
        $this->assertDatabaseMissing('music_album_aggregated_tags', ['album_id' => $album->id]);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', ['artist_id' => $artist->id]);
        $this->assertSame('Metallica', $artist->fresh()->name);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tests\Unit;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Data\DTO\SyncUserTagsDto;
use App\Containers\MusicSection\Tag\Models\MusicAlbumAggregatedTag;
use App\Containers\MusicSection\Tag\Models\MusicArtistAggregatedTag;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Containers\MusicSection\Tag\Models\MusicUserAlbumAggregatedTag;
use App\Containers\MusicSection\Tag\Models\MusicUserArtistAggregatedTag;
use App\Containers\MusicSection\Tag\Models\MusicUserTag;
use App\Containers\MusicSection\Tag\Tasks\RecalculateAlbumAggregatedTagsTask;
use App\Containers\MusicSection\Tag\Tasks\RecalculateArtistAggregatedTagsTask;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use App\Containers\MusicSection\Tag\Tasks\SyncUserTagsTask;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecalculateAggregatedTagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_album_aggregation_counts_tagged_tracks_and_percentage(): void
    {
        [$album, $artist, $tracks, $metal, $dark] = $this->seedCatalog();

        $tracks[0]->tags()->attach($metal->id);
        $tracks[1]->tags()->attach([$metal->id, $dark->id]);

        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);

        $metalAgg = MusicAlbumAggregatedTag::query()
            ->where('album_id', $album->id)
            ->where('tag_id', $metal->id)
            ->first();
        $darkAgg = MusicAlbumAggregatedTag::query()
            ->where('album_id', $album->id)
            ->where('tag_id', $dark->id)
            ->first();

        $this->assertNotNull($metalAgg);
        $this->assertSame(2, $metalAgg->tracks_count);
        $this->assertEquals(66.67, (float) $metalAgg->percentage);
        $this->assertNotNull($darkAgg);
        $this->assertSame(1, $darkAgg->tracks_count);
        $this->assertEquals(33.33, (float) $darkAgg->percentage);
        $this->assertSame(2, MusicAlbumAggregatedTag::query()->where('album_id', $album->id)->count());
        $this->assertNotNull($artist);
    }

    public function test_album_aggregation_removes_stale_rows(): void
    {
        [$album, , $tracks, $metal, $dark] = $this->seedCatalog();

        $tracks[0]->tags()->attach([$metal->id, $dark->id]);
        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);

        $tracks[0]->tags()->detach($dark->id);
        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);

        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
            'tracks_count' => 1,
        ]);
        $this->assertDatabaseMissing('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $dark->id,
        ]);
    }

    public function test_artist_aggregation_uses_tracks_linked_through_pivot(): void
    {
        [$album, $artist, $tracks, $metal] = $this->seedCatalog();

        $tracks[0]->tags()->attach($metal->id);
        $tracks[1]->tags()->attach($metal->id);
        $tracks[2]->tags()->attach($metal->id);

        app(RecalculateArtistAggregatedTagsTask::class)->run($artist->id);

        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
        $this->assertSame(1, MusicArtistAggregatedTag::query()->where('artist_id', $artist->id)->count());
        $this->assertNotNull($album);
    }

    public function test_syncing_tags_on_album_attaches_them_to_all_tracks_then_recalculates(): void
    {
        [$album, $artist, $tracks, $metal] = $this->seedCatalog();

        app(SyncTagsTask::class)->run($album, SyncTagsDto::from(['tags' => [$metal->id]]));

        foreach ($tracks as $track) {
            $this->assertTrue($track->fresh()->tags->contains('id', $metal->id));
        }

        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
    }

    public function test_syncing_tags_on_artist_attaches_them_to_all_artist_tracks(): void
    {
        [, $artist, $tracks, $metal] = $this->seedCatalog();

        app(SyncTagsTask::class)->run($artist, SyncTagsDto::from(['tags' => [$metal->id]]));

        foreach ($tracks as $track) {
            $this->assertTrue($track->fresh()->tags->contains('id', $metal->id));
        }

        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
    }

    public function test_user_album_and_artist_aggregation(): void
    {
        $user = User::factory()->create();
        [$album, $artist, $tracks] = $this->seedCatalog();
        $userTag = MusicUserTag::query()->create([
            'user_id' => $user->id,
            'name' => 'favorite',
            'color' => '#ff0000',
        ]);

        app(SyncUserTagsTask::class)->run(
            $tracks[0],
            SyncUserTagsDto::from(['user_id' => $user->id, 'tags' => [$userTag->id]]),
        );

        $this->assertDatabaseHas('music_user_track_tag', [
            'user_id' => $user->id,
            'track_id' => $tracks[0]->id,
            'user_tag_id' => $userTag->id,
        ]);
        $this->assertDatabaseHas('music_user_album_aggregated_tags', [
            'user_id' => $user->id,
            'album_id' => $album->id,
            'tag_id' => $userTag->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);
        $this->assertDatabaseHas('music_user_artist_aggregated_tags', [
            'user_id' => $user->id,
            'artist_id' => $artist->id,
            'tag_id' => $userTag->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);
        $this->assertSame(1, MusicUserAlbumAggregatedTag::query()->count());
        $this->assertSame(1, MusicUserArtistAggregatedTag::query()->count());
    }

    public function test_syncing_user_tags_on_album_applies_to_all_tracks(): void
    {
        $user = User::factory()->create();
        [$album, $artist, $tracks] = $this->seedCatalog();
        $userTag = MusicUserTag::query()->create([
            'user_id' => $user->id,
            'name' => 'mood-night',
        ]);

        app(SyncUserTagsTask::class)->run(
            $album,
            SyncUserTagsDto::from(['user_id' => $user->id, 'tags' => [$userTag->id]]),
        );

        $this->assertSame(3, $userTag->fresh()->tracks()->count());
        $this->assertDatabaseHas('music_user_album_aggregated_tags', [
            'user_id' => $user->id,
            'album_id' => $album->id,
            'tag_id' => $userTag->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_user_artist_aggregated_tags', [
            'user_id' => $user->id,
            'artist_id' => $artist->id,
            'tag_id' => $userTag->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
        $this->assertCount(3, $tracks);
    }

    public function test_soft_deleted_tracks_are_excluded_from_counts(): void
    {
        [$album, $artist, $tracks, $metal] = $this->seedCatalog();
        $tracks[0]->tags()->attach($metal->id);
        $tracks[1]->tags()->attach($metal->id);
        $tracks[0]->delete();

        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);
        app(RecalculateArtistAggregatedTagsTask::class)->run($artist->id);

        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
            'tracks_count' => 1,
            'percentage' => '50.00',
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
            'tracks_count' => 1,
            'percentage' => '50.00',
        ]);
    }

    /**
     * @return array{0: Album, 1: Artist, 2: list<Track>, 3: MusicTag, 4: MusicTag}
     */
    private function seedCatalog(): array
    {
        $user = User::factory()->create();
        $group = MusicTagGroup::query()->create([
            'name' => 'Genre',
            'is_system' => true,
        ]);
        $metal = MusicTag::query()->create([
            'name' => 'Metal',
            'group_id' => $group->id,
            'is_active' => true,
        ]);
        $dark = MusicTag::query()->create([
            'name' => 'Dark',
            'group_id' => $group->id,
            'is_active' => true,
        ]);

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

        $tracks = [];
        foreach (['Enter Sandman', 'Sad But True', 'Holier Than Thou'] as $index => $name) {
            $track = Track::query()->create([
                'album_id' => $album->id,
                'name' => $name,
                'number' => $index + 1,
            ]);
            $track->artists()->attach($artist->id);
            $tracks[] = $track;
        }

        return [$album, $artist, $tracks, $metal, $dark];
    }
}

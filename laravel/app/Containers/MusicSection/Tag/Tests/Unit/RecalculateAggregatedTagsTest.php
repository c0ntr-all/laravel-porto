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
use App\Containers\MusicSection\Track\UI\Actions\UpdateTrackAction;
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

    public function test_track_tag_add_change_and_delete_recalculates_album_and_artist(): void
    {
        [$album, $artist, $tracks, $metal, $dark] = $this->seedCatalog();
        $sync = app(SyncTagsTask::class);

        $sync->run($tracks[0], SyncTagsDto::from(['tags' => [$metal->id]]));

        $this->assertDatabaseHas('music_track_tag', [
            'track_id' => $tracks[0]->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);

        $sync->run($tracks[0], SyncTagsDto::from(['tags' => [$dark->id]]));

        $this->assertDatabaseMissing('music_track_tag', [
            'track_id' => $tracks[0]->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseHas('music_track_tag', [
            'track_id' => $tracks[0]->id,
            'tag_id' => $dark->id,
        ]);
        $this->assertDatabaseMissing('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $dark->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $dark->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);

        $sync->run($tracks[0], SyncTagsDto::from(['tags' => [$dark->id, $metal->id]]));

        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $dark->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);

        $sync->run($tracks[0], SyncTagsDto::from(['tags' => []]));

        $this->assertDatabaseMissing('music_track_tag', ['track_id' => $tracks[0]->id]);
        $this->assertSame(0, MusicAlbumAggregatedTag::query()->where('album_id', $album->id)->count());
        $this->assertSame(0, MusicArtistAggregatedTag::query()->where('artist_id', $artist->id)->count());
    }

    public function test_clearing_track_tags_through_update_action_recalculates_album_and_artist(): void
    {
        [$album, $artist, $tracks, $metal] = $this->seedCatalog();
        $tracks[0]->tags()->attach($metal->id);
        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);
        app(RecalculateArtistAggregatedTagsTask::class)->run($artist->id);

        app(UpdateTrackAction::class)->handle($tracks[0], ['tags' => []]);

        $this->assertCount(0, $tracks[0]->fresh()->tags);
        $this->assertDatabaseMissing('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
        ]);
    }

    public function test_album_tag_add_change_and_delete_updates_tracks_and_artist(): void
    {
        [$album, $artist, $tracks, $metal, $dark] = $this->seedCatalog();
        $rock = $this->makeTag('Rock', (int) $metal->group_id);
        $tracks[0]->tags()->sync([$metal->id]);
        $tracks[1]->tags()->sync([$metal->id, $dark->id]);
        $sync = app(SyncTagsTask::class);

        $sync->run($album, SyncTagsDto::from(['tags' => [$metal->id, $dark->id, $rock->id]]));

        foreach ($tracks as $track) {
            $this->assertTrue($track->fresh()->tags->contains('id', $rock->id));
        }
        $this->assertFalse($tracks[2]->fresh()->tags->contains('id', $metal->id));
        $this->assertTrue($tracks[1]->fresh()->tags->contains('id', $dark->id));
        $this->assertFalse($tracks[0]->fresh()->tags->contains('id', $dark->id));
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $rock->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
            'tracks_count' => 2,
            'percentage' => '66.67',
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $rock->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $dark->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);

        $sync->run($album, SyncTagsDto::from(['tags' => [$metal->id, $rock->id]]));

        $this->assertFalse($tracks[1]->fresh()->tags->contains('id', $dark->id));
        $this->assertTrue($tracks[0]->fresh()->tags->contains('id', $metal->id));
        $this->assertTrue($tracks[2]->fresh()->tags->contains('id', $rock->id));
        $this->assertDatabaseMissing('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $dark->id,
        ]);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $dark->id,
        ]);

        $sync->run($album, SyncTagsDto::from(['tags' => []]));

        foreach ($tracks as $track) {
            $this->assertCount(0, $track->fresh()->tags);
        }
        $this->assertSame(0, MusicAlbumAggregatedTag::query()->where('album_id', $album->id)->count());
        $this->assertSame(0, MusicArtistAggregatedTag::query()->where('artist_id', $artist->id)->count());
    }

    public function test_artist_tag_add_change_and_delete_updates_tracks_and_albums(): void
    {
        [$album, $artist, $tracks, $metal, $dark] = $this->seedCatalog();
        $rock = $this->makeTag('Rock', (int) $metal->group_id);
        $albumTwo = Album::query()->create([
            'name' => 'Ride the Lightning',
            'path' => 'F:\\Music\\Metallica\\Ride the Lightning',
            'album_type_id' => 1,
        ]);
        $albumTwo->artists()->attach($artist->id);
        $extra = Track::query()->create([
            'album_id' => $albumTwo->id,
            'name' => 'Fight Fire with Fire',
            'number' => 1,
        ]);
        $extra->artists()->attach($artist->id);
        $tracks[0]->tags()->sync([$metal->id]);
        $extra->tags()->sync([$dark->id]);
        $sync = app(SyncTagsTask::class);

        $sync->run($artist, SyncTagsDto::from(['tags' => [$metal->id, $dark->id, $rock->id]]));

        foreach ([...$tracks, $extra] as $track) {
            $this->assertTrue($track->fresh()->tags->contains('id', $rock->id));
        }
        $this->assertFalse($extra->fresh()->tags->contains('id', $metal->id));
        $this->assertFalse($tracks[0]->fresh()->tags->contains('id', $dark->id));
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $rock->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);
        $this->assertDatabaseMissing('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $dark->id,
        ]);
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $albumTwo->id,
            'tag_id' => $dark->id,
            'tracks_count' => 1,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $albumTwo->id,
            'tag_id' => $rock->id,
            'tracks_count' => 1,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $rock->id,
            'tracks_count' => 4,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
            'tracks_count' => 1,
            'percentage' => '25.00',
        ]);

        $sync->run($artist, SyncTagsDto::from(['tags' => [$dark->id, $rock->id]]));

        $this->assertFalse($tracks[0]->fresh()->tags->contains('id', $metal->id));
        $this->assertTrue($extra->fresh()->tags->contains('id', $dark->id));
        $this->assertDatabaseMissing('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseHas('music_album_aggregated_tags', [
            'album_id' => $albumTwo->id,
            'tag_id' => $dark->id,
            'tracks_count' => 1,
        ]);

        $sync->run($artist, SyncTagsDto::from(['tags' => []]));

        foreach ([...$tracks, $extra] as $track) {
            $this->assertCount(0, $track->fresh()->tags);
        }
        $this->assertSame(0, MusicAlbumAggregatedTag::query()->where('album_id', $album->id)->count());
        $this->assertSame(0, MusicAlbumAggregatedTag::query()->where('album_id', $albumTwo->id)->count());
        $this->assertSame(0, MusicArtistAggregatedTag::query()->where('artist_id', $artist->id)->count());
    }

    public function test_artist_tag_delete_recalculates_artists_sharing_the_track(): void
    {
        [$album, $artist, $tracks, $metal] = $this->seedCatalog();
        $other = Artist::query()->create([
            'user_id' => $artist->user_id,
            'name' => 'Guest',
            'path' => 'F:\\Music\\Guest',
        ]);
        $tracks[0]->artists()->attach($other->id);
        $tracks[0]->tags()->sync([$metal->id]);
        app(RecalculateArtistAggregatedTagsTask::class)->run($artist->id);
        app(RecalculateArtistAggregatedTagsTask::class)->run($other->id);
        app(RecalculateAlbumAggregatedTagsTask::class)->run($album->id);

        app(SyncTagsTask::class)->run($artist, SyncTagsDto::from(['tags' => []]));

        $this->assertCount(0, $tracks[0]->fresh()->tags);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', [
            'artist_id' => $artist->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseMissing('music_artist_aggregated_tags', [
            'artist_id' => $other->id,
            'tag_id' => $metal->id,
        ]);
        $this->assertDatabaseMissing('music_album_aggregated_tags', [
            'album_id' => $album->id,
            'tag_id' => $metal->id,
        ]);
    }

    public function test_user_tag_changes_propagate_across_track_album_and_artist(): void
    {
        $user = User::factory()->create();
        [$album, $artist, $tracks] = $this->seedCatalog();
        $favorite = MusicUserTag::query()->create([
            'user_id' => $user->id,
            'name' => 'favorite',
        ]);
        $later = MusicUserTag::query()->create([
            'user_id' => $user->id,
            'name' => 'later',
        ]);
        $night = MusicUserTag::query()->create([
            'user_id' => $user->id,
            'name' => 'night',
        ]);
        $sync = app(SyncUserTagsTask::class);
        $dto = static fn (array $tags): SyncUserTagsDto => SyncUserTagsDto::from([
            'user_id' => $user->id,
            'tags' => $tags,
        ]);

        $sync->run($tracks[0], $dto([$favorite->id]));
        $this->assertDatabaseHas('music_user_album_aggregated_tags', [
            'user_id' => $user->id,
            'album_id' => $album->id,
            'tag_id' => $favorite->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);
        $this->assertDatabaseHas('music_user_artist_aggregated_tags', [
            'user_id' => $user->id,
            'artist_id' => $artist->id,
            'tag_id' => $favorite->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);

        $sync->run($tracks[0], $dto([$later->id]));
        $this->assertDatabaseMissing('music_user_album_aggregated_tags', [
            'user_id' => $user->id,
            'album_id' => $album->id,
            'tag_id' => $favorite->id,
        ]);
        $this->assertDatabaseHas('music_user_artist_aggregated_tags', [
            'user_id' => $user->id,
            'artist_id' => $artist->id,
            'tag_id' => $later->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);

        $sync->run($tracks[0], $dto([]));
        $this->assertSame(0, MusicUserAlbumAggregatedTag::query()->where('user_id', $user->id)->count());
        $this->assertSame(0, MusicUserArtistAggregatedTag::query()->where('user_id', $user->id)->count());

        $sync->run($tracks[0], $dto([$favorite->id]));
        $sync->run($tracks[1], $dto([$later->id]));
        $sync->run($album, $dto([$favorite->id, $later->id, $night->id]));

        $this->assertTrue($tracks[2]->fresh()->userTags->contains('id', $night->id));
        $this->assertFalse($tracks[2]->fresh()->userTags->contains('id', $favorite->id));
        $this->assertFalse($tracks[2]->fresh()->userTags->contains('id', $later->id));
        $this->assertDatabaseHas('music_user_album_aggregated_tags', [
            'user_id' => $user->id,
            'album_id' => $album->id,
            'tag_id' => $night->id,
            'tracks_count' => 3,
            'percentage' => '100.00',
        ]);
        $this->assertDatabaseHas('music_user_artist_aggregated_tags', [
            'user_id' => $user->id,
            'artist_id' => $artist->id,
            'tag_id' => $favorite->id,
            'tracks_count' => 1,
            'percentage' => '33.33',
        ]);

        $sync->run($album, $dto([$later->id, $night->id]));
        $this->assertDatabaseMissing('music_user_track_tag', [
            'user_id' => $user->id,
            'track_id' => $tracks[0]->id,
            'user_tag_id' => $favorite->id,
        ]);
        $this->assertDatabaseMissing('music_user_artist_aggregated_tags', [
            'user_id' => $user->id,
            'artist_id' => $artist->id,
            'tag_id' => $favorite->id,
        ]);

        $sync->run($artist, $dto([]));
        $this->assertSame(0, MusicUserAlbumAggregatedTag::query()->where('album_id', $album->id)->count());
        $this->assertSame(0, MusicUserArtistAggregatedTag::query()->where('artist_id', $artist->id)->count());
        $this->assertDatabaseMissing('music_user_track_tag', [
            'user_id' => $user->id,
            'track_id' => $tracks[1]->id,
        ]);
    }

    private function makeTag(string $name, int $groupId): MusicTag
    {
        return MusicTag::query()->create([
            'name' => $name,
            'group_id' => $groupId,
            'is_active' => true,
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

<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models\Traits;

use App\Containers\MusicSection\Tag\Jobs\RecalculateAlbumAggregatedTagsJob;
use App\Containers\MusicSection\Tag\Jobs\RecalculateArtistAggregatedTagsJob;
use App\Containers\MusicSection\Tag\Jobs\RecalculateUserAlbumAggregatedTagsJob;
use App\Containers\MusicSection\Tag\Jobs\RecalculateUserArtistAggregatedTagsJob;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Models\MusicTrackTag;
use App\Containers\MusicSection\Tag\Models\MusicUserTag;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

trait HasMusicTags
{
    /** @var array{album_id: int|null, artist_ids: list<int>, user_ids: list<int>} */
    public array $tagRecalcContext = [
        'album_id' => null,
        'artist_ids' => [],
        'user_ids' => [],
    ];

    protected static function bootHasMusicTags(): void
    {
        static::deleting(function ($item): void {
            $item->tagRecalcContext = [
                'album_id' => $item->album_id ? (int) $item->album_id : null,
                'artist_ids' => $item->artists()->pluck('music_artists.id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all(),
                'user_ids' => DB::table('music_user_track_tag')
                    ->where('track_id', $item->id)
                    ->distinct()
                    ->pluck('user_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all(),
            ];

            if (method_exists($item, 'isForceDeleting') && $item->isForceDeleting()) {
                $item->tags()->detach();
                $item->userTags()->detach();
            }
        });

        static::deleted(function ($item): void {
            $albumId = $item->tagRecalcContext['album_id'] ?? null;
            $artistIds = $item->tagRecalcContext['artist_ids'] ?? [];
            $userIds = $item->tagRecalcContext['user_ids'] ?? [];

            if ($albumId) {
                RecalculateAlbumAggregatedTagsJob::dispatch($albumId);
            }

            foreach ($artistIds as $artistId) {
                RecalculateArtistAggregatedTagsJob::dispatch($artistId);
            }

            foreach ($userIds as $userId) {
                if ($albumId) {
                    RecalculateUserAlbumAggregatedTagsJob::dispatch($userId, $albumId);
                }

                foreach ($artistIds as $artistId) {
                    RecalculateUserArtistAggregatedTagsJob::dispatch($userId, $artistId);
                }
            }
        });
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MusicTag::class, 'music_track_tag', 'track_id', 'tag_id')
                    ->using(MusicTrackTag::class)
                    ->withTimestamps();
    }

    public function userTags(): BelongsToMany
    {
        return $this->belongsToMany(MusicUserTag::class, 'music_user_track_tag', 'track_id', 'user_tag_id')
                    ->withPivot('user_id')
                    ->withTimestamps();
    }
}

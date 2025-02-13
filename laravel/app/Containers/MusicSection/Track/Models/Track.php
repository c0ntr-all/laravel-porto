<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Models;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Models\Traits\HasMusicTags;
use App\Containers\MusicSection\Track\Casts\TrackDurationCast;
use App\Ship\Helpers\ArrayHelper;
use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Music\Track
 *
 * @property string $id
 * @property int $album_id
 * @property int|null $number
 * @property string $name
 * @property int|null $cd
 * @property string|null $path
 * @property string|null $image
 * @property mixed $duration
 * @property int|null $bitrate
 * @property string|null $link
 * @property string|null $lyrics
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Album|null $album
 * @property-read string $full_image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Playlist> $playlists
 * @property-read int|null $playlists_count
 * @property-read Rate|null $rate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MusicTag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Track filter($filter)
 * @method static \Illuminate\Database\Eloquent\Builder|Track newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Track newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Track onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Track onlyWeb()
 * @method static \Illuminate\Database\Eloquent\Builder|Track query()
 * @method static \Illuminate\Database\Eloquent\Builder|Track user($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereAlbumId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereBitrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereCd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereRate($filters)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereTags($filters)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Track withoutTrashed()
 * @mixin \Eloquent
 */
class Track extends Model
{
    use SoftDeletes,
        HasMusicTags,
        HasImage;

    protected $table = 'music_tracks';

    protected $guarded = [];

    protected $casts = [
        'duration' => TrackDurationCast::class
    ];

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'music_track_artist', 'track_id', 'artist_id');
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'album_id', 'id');
    }

    public function playlists(): belongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'music_playlist_track', 'track_id', 'playlist_id');
    }

    public function rate(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    public function scopeUser($query, $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function scopeWhereTags($query, $filters): void
    {
        $union = $filters['union'] ?? true;

        if (!empty($filters['tags'])) {
            $filters['tags'] = array_column($filters['tags'], 'value');
            if ($filters['type'] == 'hierarchical') {
                $hierarchyTags = $this->getTagsHierarchy($filters['tags']);
                $filters['tags'] = array_merge(...$hierarchyTags->toArray());
            }
        }

        if ($union) {
            foreach ($filters['tags'] as $filter) {
                $query->whereRelation('tags', 'id', $filter);
            }
        } else {
            $query->whereHas('tags', function ($query) use ($filters) {
                $query->whereIn('id', $filters['tags']);
            });
        }
    }

    public function scopeWhereRate($query, $filters): void
    {
        $query->whereHas('rate', function ($query) use ($filters) {
            $query->whereIn('rate', $filters['rate']);
        });
    }

    public function scopeOnlyWeb($query): void
    {
        $query->whereNotNull('link');
    }

    private function getTagsHierarchy($tags)
    {
        return MusicTag::whereIn('id', $tags)->get()->map(function($item) {
            $tagsList = ArrayHelper::flattenArray($item->childrenCategories->toArray());

            return array_column($tagsList, 'id');
        });
    }

    public static function filterWithCursor(array $filters = [])
    {
        $query = static::orderBy('created_at', 'DESC');

        if (!empty($filters['tags'])) {
            $query = $query->whereTags($filters);
        }

        if (!empty($filters['rate'])) {
            $query = $query->whereRate($filters);
        }

        if (!empty($filters['tracks'])) {
            $query = $query->onlyWeb();
        }

        return $query->cursorPaginate(50);
    }
}

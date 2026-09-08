<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Models;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Tag\Models\MusicArtistAggregatedTag;
use App\Containers\MusicSection\Tag\Models\MusicUserArtistAggregatedTag;
use App\Containers\MusicSection\Tag\Models\Traits\HasAggregatedMusicTags;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Music\Artist
 *
 * @property int $id
 * @property string $name
 * @property string|null $country
 * @property string|null $description
 * @property string|null $image
 * @property string $path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Album> $albums
 * @property-read int|null $albums_count
 * @property-read string $full_image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MusicTag> $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Track> $tracks
 * @property-read int|null $tracks_count
 * @method static \Database\Factories\Music\ArtistFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Artist filter($filter)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Artist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Artist onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Artist query()
 * @method static \Illuminate\Database\Eloquent\Builder|Artist whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artist withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Artist withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Artist updateOrCreate($attributes, $values)
 * @mixin \Eloquent
 */
class Artist extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasAggregatedMusicTags;
    use HasImage;

    protected $table = 'music_artists';

    protected $fillable = [
        'user_id',
        'country_id',
        'name',
        'description',
        'image',
        'path',
    ];

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class, 'music_album_artist', 'artist_id', 'album_id');
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'music_track_artist', 'artist_id', 'track_id')
                    ->withPivot(['is_author', 'role']);
    }

    public function aggregatedTags(): HasMany
    {
        return $this->hasMany(MusicArtistAggregatedTag::class, 'artist_id');
    }

    public function userAggregatedTags(): HasMany
    {
        return $this->hasMany(MusicUserArtistAggregatedTag::class, 'artist_id');
    }

    protected function aggregatedTagsTable(): string
    {
        return 'music_artist_aggregated_tags';
    }

    protected function aggregatedTagsForeignKey(): string
    {
        return 'artist_id';
    }

    protected function userAggregatedTagsTable(): string
    {
        return 'music_user_artist_aggregated_tags';
    }
}

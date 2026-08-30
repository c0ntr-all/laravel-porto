<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Models;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Models\MusicAlbumAggregatedTag;
use App\Containers\MusicSection\Tag\Models\MusicUserAlbumAggregatedTag;
use App\Containers\MusicSection\Tag\Models\Traits\HasAggregatedMusicTags;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Music\Album
 *
 * @property int $id
 * @property int $parent_id
 * @property int $album_type_id
 * @property string $attributes
 * @property string $name
 * @property string $description
 * @property Carbon|null $date
 * @property string|null $content
 * @property string|null $image
 * @property string $path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Music\Artist $artist
 * @property-read string $full_image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Music\MusicTag> $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Music\Track> $tracks
 * @property-read int|null $tracks_count
 * @method static \Database\Factories\Music\AlbumFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Album newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Album newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Album onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Album query()
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereArtistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereEdition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Album withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Album withoutTrashed()
 * @mixin \Eloquent
 */
class Album extends Model
{
    use SoftDeletes,
        HasAggregatedMusicTags,
        HasImage;

    protected $table = 'music_albums';

    protected $fillable = [
        'parent_id',
        'album_type_id',
        'name',
        'description',
        'attributes',
        'edition',
        'date',
        'is_date_verified',
        'image',
        'path',
    ];
    protected $casts = [
        'date' => 'datetime',
        'attributes' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Album $album): void {
            if (method_exists($album, 'isForceDeleting') && $album->isForceDeleting()) {
                return;
            }

            $album->versions()->update(['parent_id' => null]);
        });
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'music_album_artist', 'album_id', 'artist_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_id');
    }

    /**
     * Direct versions of this album. Nesting is limited to one level.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_id', 'id')
                    ->orderBy('date')
                    ->orderBy('name');
    }

    public function albumType(): BelongsTo
    {
        return $this->belongsTo(AlbumType::class, 'album_type_id');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class, 'album_id', 'id');
    }

    public function aggregatedTags(): HasMany
    {
        return $this->hasMany(MusicAlbumAggregatedTag::class, 'album_id');
    }

    public function userAggregatedTags(): HasMany
    {
        return $this->hasMany(MusicUserAlbumAggregatedTag::class, 'album_id');
    }

    protected function aggregatedTagsTable(): string
    {
        return 'music_album_aggregated_tags';
    }

    protected function aggregatedTagsForeignKey(): string
    {
        return 'album_id';
    }

    protected function userAggregatedTagsTable(): string
    {
        return 'music_user_album_aggregated_tags';
    }
}

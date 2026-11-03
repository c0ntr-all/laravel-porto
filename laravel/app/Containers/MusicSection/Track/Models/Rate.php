<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Music\Rate
 *
 * @property int $user_id
 * @property int $track_id
 * @property int $rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereUserId($value)
 * @mixin \Eloquent
 */
class Rate extends Model
{
    protected $table = 'music_track_rates';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'track_id',
        'rate',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', '=', auth()->id());
            }
        });
    }

    /**
     * The table primary key is (user_id, track_id). Eloquent's default `id`
     * produces `where id is null` on update.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('user_id', '=', $this->keyValue('user_id'))
            ->where('track_id', '=', $this->keyValue('track_id'));

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    protected function setKeysForSelectQuery($query)
    {
        return $this->setKeysForSaveQuery($query);
    }

    private function keyValue(string $key): mixed
    {
        if (array_key_exists($key, $this->original)) {
            return $this->original[$key];
        }

        return $this->getAttribute($key);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Music\Rate
 *
 * @property int $id
 * @property int $user_id
 * @property int $track_id
 * @property int $rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereUserId($value)
 * @mixin \Eloquent
 */
class Rate extends Model
{
    protected $table = 'music_track_rates';

    protected $guarded = [];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('user', function ($builder) {
            $builder->where('user_id', '=', auth()->user()->id);
        });
    }
}

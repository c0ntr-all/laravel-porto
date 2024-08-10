<?php

namespace App\Containers\MusicSection\Track\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Music\MusicHistory
 *
 * @property int $user_id
 * @property int $track_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Music\Track $track
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\Music\MusicHistoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|History newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|History newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|History onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|History query()
 * @method static \Illuminate\Database\Eloquent\Builder|History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereUser(string $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|History withoutTrashed()
 * @mixin \Eloquent
 */
class History extends Model
{
    use HasFactory,
        HasUser,
        SoftDeletes;

    protected $table = 'music_history';

    protected $fillable = [
        'user_id',
        'track_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}

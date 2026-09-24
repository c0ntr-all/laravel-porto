<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Models;

use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $season_id
 * @property Carbon $watched_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Season $season
 */
class SeasonWatch extends Model
{
    use HasFactory;

    protected $table = 'movie_season_watches';

    protected $fillable = [
        'user_id',
        'season_id',
        'watched_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'season_id' => 'integer',
            'watched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}

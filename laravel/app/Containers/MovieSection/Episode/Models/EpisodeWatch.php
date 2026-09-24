<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Models;

use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $episode_id
 * @property Carbon $watched_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Episode $episode
 */
class EpisodeWatch extends Model
{
    use HasFactory;

    protected $table = 'movie_episode_watches';

    protected $fillable = [
        'user_id',
        'episode_id',
        'watched_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'episode_id' => 'integer',
            'watched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}

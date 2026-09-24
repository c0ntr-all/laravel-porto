<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Models;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $movie_id
 * @property int|null $kp_id
 * @property int|null $kp_season_id
 * @property int|null $kp_movie_id
 * @property string|null $name
 * @property string|null $en_name
 * @property int $number
 * @property Carbon|null $air_date
 * @property int|null $episodes_count
 * @property int|null $duration
 * @property string|null $poster
 * @property string|null $poster_preview
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Movie $movie
 * @property-read Collection<int, Episode> $episodes
 * @property-read Collection<int, SeasonWatch> $watches
 */
class Season extends Model
{
    use HasFactory;

    protected $table = 'movie_seasons';

    protected $fillable = [
        'movie_id',
        'kp_id',
        'kp_season_id',
        'kp_movie_id',
        'name',
        'en_name',
        'number',
        'air_date',
        'episodes_count',
        'duration',
        'poster',
        'poster_preview',
    ];

    protected function casts(): array
    {
        return [
            'movie_id' => 'integer',
            'kp_id' => 'integer',
            'kp_season_id' => 'integer',
            'kp_movie_id' => 'integer',
            'number' => 'integer',
            'air_date' => 'date',
            'episodes_count' => 'integer',
            'duration' => 'integer',
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'season_id')->orderBy('number')->orderBy('id');
    }

    public function watches(): HasMany
    {
        return $this->hasMany(SeasonWatch::class, 'season_id');
    }

    /**
     * @return \Closure(\Illuminate\Database\Eloquent\Relations\HasMany): void
     */
    public static function constrainWatchesToCurrentUser(): \Closure
    {
        $userId = auth()->id();

        return static function ($query) use ($userId): void {
            if ($userId === null) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where('user_id', $userId);
        };
    }
}

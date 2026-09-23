<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Models;

use App\Containers\MovieSection\Season\Models\Season;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $season_id
 * @property int|null $kp_id
 * @property int|null $kp_season_id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $en_description
 * @property int $number
 * @property int|null $duration
 * @property Carbon|null $air_date
 * @property string|null $still
 * @property string|null $still_preview
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Season $season
 */
class Episode extends Model
{
    use HasFactory;

    protected $table = 'movie_episodes';

    protected $fillable = [
        'season_id',
        'kp_id',
        'kp_season_id',
        'name',
        'description',
        'en_description',
        'number',
        'duration',
        'air_date',
        'still',
        'still_preview',
    ];

    protected function casts(): array
    {
        return [
            'season_id' => 'integer',
            'kp_id' => 'integer',
            'kp_season_id' => 'integer',
            'number' => 'integer',
            'duration' => 'integer',
            'air_date' => 'date',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}

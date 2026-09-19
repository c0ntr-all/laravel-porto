<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Models;

use App\Containers\AppSection\Country\Models\Country;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Genre\Models\Traits\HasGenres;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $kp_id
 * @property string $title
 * @property string|null $description
 * @property int $year
 * @property MovieTypeEnum $type
 * @property string|null $cover
 * @property string|null $kp_rating
 * @property string|null $kp_img
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Genre> $genres
 * @property-read Collection<int, Country> $countries
 */
class Movie extends Model
{
    use HasFactory;
    use HasGenres;

    protected $table = 'movies';

    protected $fillable = [
        'kp_id',
        'title',
        'description',
        'year',
        'type',
        'cover',
        'kp_rating',
        'kp_img',
    ];

    protected function casts(): array
    {
        return [
            'kp_id' => 'integer',
            'year' => 'integer',
            'type' => MovieTypeEnum::class,
            'kp_rating' => 'decimal:1',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(fn (Movie $movie) => $movie->countries()->detach());
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(
            Country::class,
            'movie_country',
            'movie_id',
            'country_id',
        )->withTimestamps();
    }
}

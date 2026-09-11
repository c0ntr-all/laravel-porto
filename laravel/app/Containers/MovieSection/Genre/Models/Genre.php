<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Models;

use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $kp_id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Movie> $movies
 */
class Genre extends Model
{
    use HasFactory;

    protected $table = 'movie_genres';

    protected $fillable = [
        'kp_id',
        'name',
        'slug',
    ];

    protected function casts(): array
    {
        return [
            'kp_id' => 'integer',
        ];
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_genre', 'genre_id', 'movie_id')
            ->withTimestamps();
    }
}

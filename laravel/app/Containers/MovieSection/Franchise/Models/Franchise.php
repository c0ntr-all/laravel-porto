<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string|null $image
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $full_image
 * @property-read User $user
 * @property-read Collection<int, Movie> $movies
 */
class Franchise extends Model
{
    use HasFactory;
    use HasImage;
    use SoftDeletes;

    protected $table = 'movie_franchises';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'image',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(
            Movie::class,
            'movie_franchise_movie',
            'franchise_id',
            'movie_id',
        )
            ->withPivot(['order'])
            ->withTimestamps()
            ->orderBy('movie_franchise_movie.order')
            ->orderBy('movies.id');
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Folder\Support\FolderMoviesCountCache;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $slug
 * @property bool $is_system
 * @property int $movies_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, Movie> $movies
 */
class Folder extends Model
{
    use HasFactory;

    protected $table = 'movie_folders';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'is_system',
        'movies_count',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_system' => 'boolean',
            'movies_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Folder $folder): void {
            FolderMoviesCountCache::forget((int) $folder->id);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(
            Movie::class,
            'movie_folder_movie',
            'folder_id',
            'movie_id',
        )
            ->withPivot(['added_at'])
            ->withTimestamps();
    }
}

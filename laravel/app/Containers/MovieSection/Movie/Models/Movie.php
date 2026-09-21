<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Models;

use App\Containers\AppSection\Country\Models\Country;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Support\FolderMoviesCountCache;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Genre\Models\Traits\HasGenres;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Person\Models\Person;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int|null $kp_id
 * @property string $title
 * @property string|null $description
 * @property string|null $short_description
 * @property int|null $year
 * @property MovieTypeEnum $type
 * @property string|null $cover
 * @property string|null $kp_rating
 * @property string|null $kp_img
 * @property Carbon|null $kp_imported_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Genre> $genres
 * @property-read Collection<int, Country> $countries
 * @property-read Collection<int, Person> $persons
 * @property-read Collection<int, Folder> $folders
 * @property-read Collection<int, MoviePersonCredit> $credits
 * @property-read int $actors_count
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
        'short_description',
        'year',
        'type',
        'cover',
        'kp_rating',
        'kp_img',
        'kp_imported_at',
    ];

    protected $attributes = [
        'type' => MovieTypeEnum::MOVIE->value,
    ];

    protected function casts(): array
    {
        return [
            'kp_id' => 'integer',
            'year' => 'integer',
            'type' => MovieTypeEnum::class,
            'kp_rating' => 'decimal:1',
            'kp_imported_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Movie $movie): void {
            $folderIds = DB::table('movie_folder_movie')
                ->where('movie_id', $movie->id)
                ->pluck('folder_id');

            $movie->countries()->detach();
            $movie->persons()->detach();
            $movie->folders()->detach();

            Folder::query()
                ->whereIn('id', $folderIds)
                ->get()
                ->each(static function (Folder $folder): void {
                    FolderMoviesCountCache::refresh($folder);
                });

            DB::table('lifelog_post_subjectables')
                ->where('subjectable_type', $movie->getMorphClass())
                ->where('subjectable_id', $movie->getKey())
                ->delete();
        });
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

    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'movie_person',
            'movie_id',
            'person_id',
        )
            ->withPivot(['profession_id', 'description'])
            ->withTimestamps();
    }

    public function credits(): HasMany
    {
        return $this->hasMany(MoviePersonCredit::class, 'movie_id')->orderBy('id');
    }

    public function folders(): BelongsToMany
    {
        return $this->belongsToMany(
            Folder::class,
            'movie_folder_movie',
            'movie_id',
            'folder_id',
        )
            ->withPivot(['added_at'])
            ->withTimestamps();
    }

    /**
     * @return \Closure(\Illuminate\Database\Eloquent\Relations\BelongsToMany): void
     */
    public static function constrainFoldersToCurrentUser(): \Closure
    {
        $userId = auth()->id();

        return static function ($query) use ($userId): void {
            if ($userId === null) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where('movie_folders.user_id', $userId);
        };
    }
}

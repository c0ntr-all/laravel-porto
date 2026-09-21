<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Models;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Profession\Models\Profession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $kp_id
 * @property int|null $profession_id
 * @property string $name
 * @property string|null $en_name
 * @property string|null $photo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Profession|null $profession
 * @property-read Collection<int, Profession> $professions
 * @property-read Collection<int, Movie> $movies
 */
class Person extends Model
{
    use HasFactory;

    protected $table = 'movie_persons';

    protected $fillable = [
        'kp_id',
        'profession_id',
        'name',
        'en_name',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'kp_id' => 'integer',
            'profession_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Person $person): void {
            $person->movies()->detach();
            $person->professions()->detach();
        });
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'profession_id');
    }

    public function professions(): BelongsToMany
    {
        return $this->belongsToMany(
            Profession::class,
            'movie_person_profession',
            'person_id',
            'profession_id',
        )->withTimestamps();
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(
            Movie::class,
            'movie_person',
            'person_id',
            'movie_id',
        )
            ->withPivot(['profession_id', 'description'])
            ->withTimestamps();
    }
}

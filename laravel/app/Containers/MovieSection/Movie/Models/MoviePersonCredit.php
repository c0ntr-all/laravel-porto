<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Models;

use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Profession\Models\Profession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $movie_id
 * @property int $person_id
 * @property int $profession_id
 * @property string|null $description
 * @property-read Person|null $person
 * @property-read Profession|null $profession
 * @property-read Movie|null $movie
 */
class MoviePersonCredit extends Model
{
    protected $table = 'movie_person';

    protected $fillable = [
        'movie_id',
        'person_id',
        'profession_id',
        'description',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }
}

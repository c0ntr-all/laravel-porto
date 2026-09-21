<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Models;

use App\Containers\MovieSection\Person\Models\Person;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $en_name
 * @property string|null $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Person> $persons
 */
class Profession extends Model
{
    use HasFactory;

    protected $table = 'movie_professions';

    protected $fillable = [
        'en_name',
        'name',
    ];

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class, 'profession_id');
    }
}

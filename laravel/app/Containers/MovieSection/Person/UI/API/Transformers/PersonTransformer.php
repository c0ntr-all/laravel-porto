<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\API\Transformers;

use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Profession\UI\API\Transformers\ProfessionTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class PersonTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'profession',
        'professions',
        'movies',
    ];

    public function transform(Person $person): array
    {
        return [
            'id' => $person->id,
            'kp_id' => $person->kp_id,
            'profession_id' => $person->profession_id,
            'name' => $person->name,
            'en_name' => $person->en_name,
            'photo' => $person->photo,
            'created_at' => $person->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $person->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeProfession(Person $person): Item|NullResource
    {
        if ($person->profession === null) {
            return $this->null();
        }

        return $this->item($person->profession, new ProfessionTransformer(), ContainerAliasEnum::MOVIE_PROFESSION->value);
    }

    public function includeProfessions(Person $person): Collection
    {
        $person->loadMissing('professions');

        return $this->collection($person->professions, new ProfessionTransformer(), ContainerAliasEnum::MOVIE_PROFESSION->value);
    }

    public function includeMovies(Person $person): Collection
    {
        $person->loadMissing('movies');

        return $this->collection($person->movies, new MovieTransformer(), ContainerAliasEnum::MOVIE->value);
    }
}

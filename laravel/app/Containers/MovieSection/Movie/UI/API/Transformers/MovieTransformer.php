<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\API\Transformers;

use App\Containers\AppSection\Country\UI\API\Transformers\CountryTransformer;
use App\Containers\MovieSection\Genre\UI\API\Transformers\GenreTransformer;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Person\UI\API\Transformers\PersonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class MovieTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'genres',
        'countries',
        'persons',
    ];

    public function transform(Movie $movie): array
    {
        return [
            'id' => $movie->id,
            'kp_id' => $movie->kp_id,
            'title' => $movie->title,
            'description' => $movie->description,
            'short_description' => $movie->short_description,
            'year' => $movie->year,
            'type' => $movie->type->value,
            'cover' => $movie->cover,
            'kp_rating' => $movie->kp_rating !== null ? (float) $movie->kp_rating : null,
            'kp_img' => $movie->kp_img,
            'created_at' => $movie->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $movie->updated_at?->format('Y-m-d H:i:s'),
            'credits' => $this->mapCredits($movie),
        ];
    }

    public function includeGenres(Movie $movie): Collection
    {
        return $this->collection($movie->genres, new GenreTransformer(), ContainerAliasEnum::MOVIE_GENRE->value);
    }

    public function includeCountries(Movie $movie): Collection
    {
        return $this->collection($movie->countries, new CountryTransformer(), ContainerAliasEnum::COUNTRY->value);
    }

    public function includePersons(Movie $movie): Collection
    {
        $movie->loadMissing('persons');

        return $this->collection($movie->persons, new PersonTransformer(), ContainerAliasEnum::MOVIE_PERSON->value);
    }

    /**
     * @return list<array{
     *     id: int,
     *     description: string|null,
     *     person: array{id: int, name: string, en_name: string|null, photo: string|null},
     *     profession: array{id: int, en_name: string, name: string|null}
     * }>
     */
    private function mapCredits(Movie $movie): array
    {
        if (!$movie->relationLoaded('credits')) {
            return [];
        }

        $credits = [];

        foreach ($movie->credits as $credit) {
            if ($credit->person === null || $credit->profession === null) {
                continue;
            }

            $credits[] = [
                'id' => $credit->id,
                'description' => $credit->description,
                'person' => [
                    'id' => $credit->person->id,
                    'name' => $credit->person->name,
                    'en_name' => $credit->person->en_name,
                    'photo' => $credit->person->photo,
                ],
                'profession' => [
                    'id' => $credit->profession->id,
                    'en_name' => $credit->profession->en_name,
                    'name' => $credit->profession->name,
                ],
            ];
        }

        return $credits;
    }
}

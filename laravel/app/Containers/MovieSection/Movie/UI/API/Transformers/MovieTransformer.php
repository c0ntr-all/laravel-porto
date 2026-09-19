<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\API\Transformers;

use App\Containers\AppSection\Country\UI\API\Transformers\CountryTransformer;
use App\Containers\MovieSection\Genre\UI\API\Transformers\GenreTransformer;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class MovieTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'genres',
        'countries',
    ];

    public function transform(Movie $movie): array
    {
        return [
            'id' => $movie->id,
            'kp_id' => $movie->kp_id,
            'title' => $movie->title,
            'description' => $movie->description,
            'year' => $movie->year,
            'type' => $movie->type->value,
            'cover' => $movie->cover,
            'kp_rating' => $movie->kp_rating !== null ? (float) $movie->kp_rating : null,
            'kp_img' => $movie->kp_img,
            'created_at' => $movie->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $movie->updated_at?->format('Y-m-d H:i:s'),
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
}

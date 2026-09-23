<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\UI\API\Transformers;

use App\Containers\MovieSection\Episode\UI\API\Transformers\EpisodeTransformer;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Containers\MovieSection\Season\Models\Season;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class SeasonTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'episodes',
        'movie',
    ];

    public function transform(Season $season): array
    {
        return [
            'id' => $season->id,
            'movie_id' => $season->movie_id,
            'kp_id' => $season->kp_id,
            'kp_season_id' => $season->kp_season_id,
            'kp_movie_id' => $season->kp_movie_id,
            'name' => $season->name,
            'en_name' => $season->en_name,
            'number' => $season->number,
            'air_date' => $season->air_date?->format('Y-m-d'),
            'episodes_count' => $season->episodes_count,
            'duration' => $season->duration,
            'poster' => $season->poster,
            'poster_preview' => $season->poster_preview,
            'created_at' => $season->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $season->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeEpisodes(Season $season): Collection
    {
        $season->loadMissing('episodes');

        return $this->collection($season->episodes, new EpisodeTransformer(), ContainerAliasEnum::MOVIE_EPISODE->value);
    }

    public function includeMovie(Season $season): Item|NullResource
    {
        if ($season->movie === null) {
            return $this->null();
        }

        return $this->item($season->movie, new MovieTransformer(), ContainerAliasEnum::MOVIE->value);
    }
}

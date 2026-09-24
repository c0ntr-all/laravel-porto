<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\API\Transformers;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Episode\Models\EpisodeWatch;
use App\Containers\MovieSection\Season\UI\API\Transformers\SeasonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class EpisodeTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'season',
    ];

    public function transform(Episode $episode): array
    {
        $watch = $this->currentUserWatch($episode);

        return [
            'id' => $episode->id,
            'season_id' => $episode->season_id,
            'kp_id' => $episode->kp_id,
            'kp_season_id' => $episode->kp_season_id,
            'name' => $episode->name,
            'description' => $episode->description,
            'en_description' => $episode->en_description,
            'number' => $episode->number,
            'duration' => $episode->duration,
            'air_date' => $episode->air_date?->format('Y-m-d'),
            'still' => $episode->still,
            'still_preview' => $episode->still_preview,
            'is_watched' => $watch !== null,
            'watched_at' => $watch?->watched_at?->format('Y-m-d H:i:s'),
            'created_at' => $episode->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $episode->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeSeason(Episode $episode): Item|NullResource
    {
        if ($episode->season === null) {
            return $this->null();
        }

        return $this->item($episode->season, new SeasonTransformer(), ContainerAliasEnum::MOVIE_SEASON->value);
    }

    private function currentUserWatch(Episode $episode): ?EpisodeWatch
    {
        if (!$episode->relationLoaded('watches')) {
            return null;
        }

        return $episode->watches->first();
    }
}

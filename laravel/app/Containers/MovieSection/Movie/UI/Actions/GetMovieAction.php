<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\Actions;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetMovieAction extends BaseAction
{
    public const ACTOR_PROFESSION = 'actor';
    public const ACTORS_PREVIEW_LIMIT = 10;

    public function handle(Movie $movie): Movie
    {
        $movie->load(['genres', 'countries']);
        $movie->loadCount([
            'credits as actors_count' => function ($query): void {
                $query->whereHas('profession', function ($profession): void {
                    $profession->where('en_name', self::ACTOR_PROFESSION);
                });
            },
        ]);

        $actorIds = $movie->credits()
            ->whereHas('profession', function ($profession): void {
                $profession->where('en_name', self::ACTOR_PROFESSION);
            })
            ->orderBy('id')
            ->limit(self::ACTORS_PREVIEW_LIMIT)
            ->pluck('id');

        $crewIds = $movie->credits()
            ->whereDoesntHave('profession', function ($profession): void {
                $profession->where('en_name', self::ACTOR_PROFESSION);
            })
            ->orderBy('id')
            ->pluck('id');

        $previewIds = $crewIds->concat($actorIds)->unique()->values();

        $movie->setRelation(
            'credits',
            $previewIds->isEmpty()
                ? $movie->credits()->newCollection()
                : $movie->credits()
                    ->with(['person', 'profession'])
                    ->whereIn('id', $previewIds)
                    ->orderBy('id')
                    ->get(),
        );

        return $movie;
    }

    public function asController(Movie $movie, GetRequest $request): JsonResponse
    {
        $movie = $this->handle($movie);

        return fractal($movie, new MovieTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE->value)
            ->parseIncludes(['genres', 'countries'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

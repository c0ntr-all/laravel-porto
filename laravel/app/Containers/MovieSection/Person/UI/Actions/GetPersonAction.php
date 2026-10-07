<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\Actions;

use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Person\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Person\UI\API\Transformers\PersonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetPersonAction extends BaseAction
{
    public function handle(Person $person): Person
    {
        $person->load([
            'profession',
            'professions',
            'movies' => static fn ($query) => $query->orderByDesc('year')->orderByDesc('id'),
            'credits.movie',
            'credits.profession',
        ]);

        $person->setRelation(
            'credits',
            $person->credits
                ->sortByDesc(static fn ($credit) => [
                    $credit->movie?->year ?? 0,
                    $credit->movie?->id ?? 0,
                    $credit->id,
                ])
                ->values(),
        );

        return $person;
    }

    public function asController(Person $person, GetRequest $request): JsonResponse
    {
        $person = $this->handle($person);
        $includes = (string) $request->query('include', 'profession,professions,credits');

        return fractal($person, new PersonTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_PERSON->value)
            ->parseIncludes($includes)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

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
        return $person->load([
            'profession',
            'professions',
            'movies' => static fn ($query) => $query->orderByDesc('year')->orderByDesc('id'),
        ]);
    }

    public function asController(Person $person, GetRequest $request): JsonResponse
    {
        $person = $this->handle($person);
        $includes = (string) $request->query('include', 'profession,professions,movies');

        return fractal($person, new PersonTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_PERSON->value)
            ->parseIncludes($includes)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\Actions;

use App\Containers\MovieSection\Person\Data\DTO\PersonCreateData;
use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Person\Tasks\CreatePersonTask;
use App\Containers\MovieSection\Person\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Person\UI\API\Transformers\PersonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreatePersonAction extends BaseAction
{
    public function __construct(
        private readonly CreatePersonTask $createPersonTask,
    ) {
    }

    public function handle(PersonCreateData $dto): Person
    {
        return $this->createPersonTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $person = $this->handle(PersonCreateData::from($request->validated()));

        return fractal($person, new PersonTransformer())
            ->parseIncludes(['profession'])
            ->withResourceName(ContainerAliasEnum::MOVIE_PERSON->value)
            ->addMeta(['message' => 'Person created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\Actions;

use App\Containers\MovieSection\Person\Data\DTO\PersonUpdateData;
use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Person\Tasks\UpdatePersonTask;
use App\Containers\MovieSection\Person\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Person\UI\API\Transformers\PersonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdatePersonAction extends BaseAction
{
    public function __construct(
        private readonly UpdatePersonTask $updatePersonTask,
    ) {
    }

    public function handle(Person $person, PersonUpdateData $dto): Person
    {
        return $this->updatePersonTask->run($person, $dto);
    }

    public function asController(Person $person, UpdateRequest $request): JsonResponse
    {
        $person = $this->handle($person, PersonUpdateData::from($request->validated()));

        return fractal($person, new PersonTransformer())
            ->parseIncludes(['profession'])
            ->withResourceName(ContainerAliasEnum::MOVIE_PERSON->value)
            ->addMeta(['message' => 'Person updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\UI\Actions;

use App\Containers\MovieSection\Profession\Data\DTO\ProfessionCreateData;
use App\Containers\MovieSection\Profession\Models\Profession;
use App\Containers\MovieSection\Profession\Tasks\CreateProfessionTask;
use App\Containers\MovieSection\Profession\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Profession\UI\API\Transformers\ProfessionTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateProfessionAction extends BaseAction
{
    public function __construct(
        private readonly CreateProfessionTask $createProfessionTask,
    ) {
    }

    public function handle(ProfessionCreateData $dto): Profession
    {
        return $this->createProfessionTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $profession = $this->handle(ProfessionCreateData::from($request->validated()));

        return fractal($profession, new ProfessionTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_PROFESSION->value)
            ->addMeta(['message' => 'Profession created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}

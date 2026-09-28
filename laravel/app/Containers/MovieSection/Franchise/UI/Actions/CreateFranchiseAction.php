<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Data\DTO\FranchiseCreateData;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\CreateFranchiseTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateFranchiseAction extends BaseAction
{
    public function __construct(
        private readonly CreateFranchiseTask $createFranchiseTask,
    ) {
    }

    public function handle(FranchiseCreateData $dto): Franchise
    {
        return $this->createFranchiseTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['user_id'] = (int) $request->user()->id;

        $franchise = $this->handle(FranchiseCreateData::from($payload));

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['message' => 'Franchise created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}

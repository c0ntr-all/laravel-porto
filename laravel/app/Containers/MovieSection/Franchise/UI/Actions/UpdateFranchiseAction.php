<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Data\DTO\FranchiseUpdateData;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\UpdateFranchiseTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateFranchiseAction extends BaseAction
{
    public function __construct(
        private readonly UpdateFranchiseTask $updateFranchiseTask,
    ) {
    }

    public function handle(Franchise $franchise, FranchiseUpdateData $dto): Franchise
    {
        return $this->updateFranchiseTask->run($franchise, $dto);
    }

    public function asController(Franchise $franchise, UpdateRequest $request): JsonResponse
    {
        $franchise = $this->handle($franchise, FranchiseUpdateData::from($request->validated()));

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['message' => 'Franchise updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

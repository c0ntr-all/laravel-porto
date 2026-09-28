<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetFranchiseAction extends BaseAction
{
    public function handle(Franchise $franchise): Franchise
    {
        return $franchise;
    }

    public function asController(Franchise $franchise, GetRequest $request): JsonResponse
    {
        $franchise = $this->handle($franchise);

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
